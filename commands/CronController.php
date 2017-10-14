<?php
/**
 * CronController.php 
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/6/24
 */
namespace app\commands;

use app\models\Cron;
use app\models\order\Item;
use app\models\order\Order;
use app\models\order\OrderHistory;
use app\models\product\Forecasting;
use app\models\product\Product;
use app\models\product\ProductDelivered;
use app\models\shipment\Shipment;
use app\models\Website;
use app\models\cron\CronUpdateOrderPaymentStatus;
use renk\yiipal\alidayu\SendMsg;
use yii;

class CronController extends yii\console\Controller{
    public $enableCsrfValidation = false;
    /**
     * 同步物流单号
     * @return bool
     */
    public function actionSyncTrackNo(){
        $url = Yii::$app->controller->action->getUniqueId();
        $cron = Cron::find()->where(['url'=>$url,'enabled'=>1])->one();
        if(empty($cron)){
            return false;
        }
        $shipments = Shipment::findAll(['synced'=>0]);
        if(empty($shipments)){
            return false;
        }

        $cron->last_run_time = time();
        $cron->save();
        foreach($shipments as $shipment){
            $order = Order::findOne($shipment->order_id);
            if(empty($order) || $order->status!=Item::TASK_STATUS_SHIPPED || empty($order->shipping_track_no)){
                continue;
            }

            $website = Website::findOne([$order->store_id]);
            if(empty($website) || !isset($website->url)){
                continue;
            }
            $url = $website->url . '/erp/order/Shipment';
            if($order->shipping_method == "EUB"){
                $title = 'USPS (Postal Service)';
            }else{
                $title = $order->shipping_method;
            }
            $post_data = array ("id" => $order->increment_id, "number" => $order->shipping_track_no, "title" => $title);
            try {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
                $result = curl_exec($ch);
                curl_close($ch);
                if( strtoupper($result) == 'OK' ) {
                    $shipment->synced = 1;
                    $shipment->save();
                }

            } catch(Exception $e) {

            }
        }
    }

    /**
     * 定时更新生产记录中的价格
     */
    public function actionUpdateOrderPrice(){
        $limit = 100;
        $page = 0;
        while($results = ProductDelivered::find()->where(['<','price',1])->limit($limit)->offset($page*$limit)->all()){
            foreach($results as $row){
                $product = Product::findOne($row->product_id);
                if($product->price>0){
                    $row->price = $product->price;
                    echo "Set product {$product->sku} price = {$row->price}\r\n ";
                    $row->save();
                }
            }
            $page++;
        }
    }

    /**
     * 库存预测
     */
    public function actionStartForecasting($startDate = null){
        if(empty($startDate)){
            $startDate = date('Y-m-d');
        }
        $model = new Forecasting();
        $model->startForecasting($startDate);
    }

    public function actionValidForecasting($date){
        if(empty($date)){
            $date = date('Y-m-d',strtotime("-10 days"));
        }
        $model = new Forecasting();
        $model->actualSalesValid($date);
    }

    public function actionMonitorOrders(){
        $result = Order::find()->where(['>','created_at',strtotime("-1 hour")])->andWhere(['source'=>'us'])->asArray()->one();
        if(empty($result)){
            echo '发送通知中...';
            $sendMsg = new SendMsg();
            $sendMsg->sendVoiceMsg();
        }
    }
    
    /**
     * 同步magento订单状态
     * 
     * 同步前3天的订单
     * 
     * 状态为：
     *  payment_review
     *  paypal_canceled_reversal
     *  paypal_reversed
     *  pending
     * 的订单
     * 
     * 并做历史记录
     * 
     *  /var/www/html/erp-jeulia/yii cron/update-order-price
     * 
     */
    public function actionUpdateOrderPaymentStatus(){
        //同步几天前的订单
        $start_time = 3*24*3600;
        $start_time = strtotime(date('Y-m-d',time())) - $start_time;
        
        //需要检测此状态的订单
        $payment_status = ['payment_review','paypal_canceled_reversal','paypal_reversed','pending'];
        
        //magento站点
        $source = ['us'];
        
        //magento检测地址
        //http://ops.jeulia.com/rss/order/state/statusonly/1/data/eyJpbmNyZW1lbnRfaWQiOiIxMDA0NDQ2MzkiLCJrZXkiOiJvbmVkYXkyMDE1In0=
        //添加header头：HTTP_X_REQUEST_ID=cc5811dffacbdae959eb55f074191c9d
        $magentoUrl = 'http://ops.jeulia.com/rss/order/state/statusonly/1/data/';
        
        $headers = array(
            'X-Request-ID' => 'cc5811dffacbdae959eb55f074191c9d',
            //'HTTP_X_REQUEST_ID' => 'cc5811dffacbdae959eb55f074191c9d',
        );
        
        $headerArr = array();
        foreach( $headers as $n => $v ) {
            $headerArr[] = $n .':' . $v;
        }
        
        //apikey
        $apikey = [];
        $websites = Website::find()->select(['country','security_key'])->where(['in','country',$source])->asArray()->all();
        foreach ($websites as $website){
            $apikey[$website['country']] = $website['security_key'];
        }
        
        $orders = Order::find()
                    ->select(['id','ext_order_id','increment_id','payment_status','source'])
                    ->where(['in','source',$source])
                    ->andWhere(['>','created_at',$start_time])
                    ->andWhere(['in','payment_status',$payment_status])
                    ->asArray()
                    ->all();
        
        foreach ($orders as $order){
            $data = array(
                'increment_id' => $order['increment_id'],
                'key' => $apikey[$order['source']]
            );
            
            $url = $magentoUrl.base64_encode(json_encode($data));
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArr);
            curl_setopt($ch, CURLOPT_URL, $url);
            $rss = curl_exec($ch);
            curl_close($ch);
            
            if($rss){
                $xml = simplexml_load_string($rss, null, LIBXML_NOCDATA);
                $json = json_encode($xml);
                $content = json_decode($json,TRUE);
                
                $magento_payment_status = '';
                if(isset($content['channel']['item']['description'])){
                    $magento_payment_status = strtolower(trim($content['channel']['item']['description']));
                }
                
                //更新ERP订单状态，并做更改历史记录
                if($magento_payment_status=='processing' && $order['payment_status'] != $magento_payment_status){
                    //更新ERP订单状态
                    $model = Order::findOne($order['id']);
                    $model->payment_status = $magento_payment_status;
                    $model->save();
                    
                    //做更改历史记录
                    $updateModel = new CronUpdateOrderPaymentStatus();
                    $updateModel->ext_order_id = $order['ext_order_id'];
                    $updateModel->increment_id = $order['increment_id'];
                    $updateModel->erp_payment_status = $order['payment_status'];
                    $updateModel->magento_payment_status = $magento_payment_status;
                    $updateModel->save();
                    
                    //echo $order['ext_order_id'].':'.$order['payment_status'].':'.$magento_payment_status."\n";
                }
            }
        }
    }
    
}