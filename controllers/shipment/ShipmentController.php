<?php
/**
 * ShipmentController.php 
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/9/22
 */
namespace app\controllers\shipment;
ini_set('max_execution_time', '18000000');
use app\helpers\CommonHelper;
use renk\yiipal\components\ExportData;
use yii;
use app\controllers\BaseController;
use app\models\order\Order;
use renk\yiipal\shipment\Shipment;

class ShipmentController extends BaseController{

    /**
     * 生成UPS运单
     * @return array
     * @throws \Exception
     */
    public function actionGenerate(){

        $posts = Yii::$app->request->post();
        if(!empty($posts['ids'])){
            $ids = explode(",",$posts['ids']);
            $orderModel = new Order();
            $orders = $orderModel->find()->with('items')->with('address')->where(['in','id',$ids])->distinct()->all();
            $orderIds = '';
            $invalidAddress = [];
            foreach($orders as $order){
                if($order->address->country_id != 'US'){
                    $invalidAddress[] = $order->ext_order_id;
                    continue;
                }
                if(!$this->checkUpsAddress($order->address)){
                    $invalidAddress[] = $order->ext_order_id;
                    continue;
                }
                if(!empty($order->shipping_track_no)) continue;

                if(!$this->canGenerateTrackNo($order)) continue;
                $shipment = Shipment::get($order->shipping_method);
                $shipment->create($order);
                $orderIds .=",".$order->id;
            }

            $orderIds = ltrim($orderIds,",");
            if(empty($orderIds)){
                return $this->json_output(['status'=>0,'msg'=>'请选择符合生产运单条件的订单!']);
            }
            $content = $this->renderPartial('ajax-shipment-print-modal',['orderIds'=>$orderIds,'invalidAddress'=>$invalidAddress]);
            return $this->json_output(['command'=>['method'=>'modal','data'=>$content]]);
        }else{
            return $this->json_output(['status'=>0,'msg'=>'请选择要生成物流的运单']);
        }
    }

    /**
     * Check PostCode match City in US.
     * @param $address
     * @return bool
     */
    public function checkUpsAddress($address){
        if($address->country_id != 'US'){
            return false;
        }

        if(empty($address->telephone)){
            return false;
        }

        $zipCode = require_once(Yii::$app->getVendorPath().'/renk/yiipal/helpers/US_Zip.php');
        $postcode = trim($address->postcode);
        $postcode = preg_replace("/-.*/","",$postcode);
        if(!isset($zipCode[$postcode])){
            return false;
        }

        $cities = explode(",",strtolower($zipCode[$postcode]['city']));
        $city = trim($address->city);
        $city = strtolower($city);
        if(!in_array($city,$cities)){
            return false;
        }

        return true;
    }

    /**
     * 打印发票
     * @param $orderIds
     * @return string
     */
    public function actionPrintInvoice($orderIds){
        $orderIds = explode(",",$orderIds);
        $orders = Order::find()->where(['in','id',$orderIds])->all();
        return $this->renderPartial('print-invoice-form',['orders'=>$orders]);
    }

    /**
     * 打印发票
     * @param $orderIds
     * @return string
     */
    public function actionPrintUpsCopy($orderIds){
        $orderIds = explode(",",$orderIds);
        $orders = Order::find()->where(['in','id',$orderIds])->all();
        return $this->renderPartial('print-ups-copy',['orders'=>$orders]);
    }


    /**
     * 打印面单
     * @param $orderIds
     * @return string
     */
    public function actionPrintImageLabel($orderIds){
        $orderIds = explode(",",$orderIds);
        $orders = Order::find()->where(['in','id',$orderIds])->all();
        $imageLabels = $this->buildImageLabels($orders);
        return $this->renderPartial('print-image-labels',['imageLabels'=>$imageLabels]);
    }

    /**
     * 整合所有面单
     * @param $orders
     * @return array
     */
    private function buildImageLabels($orders){
        $imageLabels = [];
        foreach($orders as $order){
            $imageLabels[] = $this->getImageLabelPath($order);
        }
        return $imageLabels;
    }

    /**
     * 获取面单图片地址
     * @param $order
     * @return string
     */
    private function getImageLabelPath($order){
        $path = '/shipment/static/'.$order->shipping_method.'/'.$order->shipping_track_no.'/label'.$order->shipping_track_no.'.gif';
        return $path;
    }

    /**
     * 判断是否可以生产运单号模板
     * @param $order
     * @return bool
     */
    private function canGenerateTrackNo($order){
        if($order->order_type == 'stock'){
            return true;
        }

        //定制单必须是等待发货或者等待验收才可以导出
        if($order->order_type == 'custom'){
            $flag = true;
            foreach($order->items as $item){
                if(in_array($item->item_status,['shipped','cancelled'])){
                    continue;
                }
                if(!in_array($item->item_status,['waiting_shipped','waiting_accept'])){
                    $flag = false;
                }
            }
            return $flag;
        }

        //淘宝单必须是等待发货才可导出
        if($order->order_type == 'taobao'){
            $flag = true;
            foreach($order->items as $item){
                if(in_array($item->item_status,['shipped','cancelled'])){
                    continue;
                }
                if(!in_array($item->item_status,['waiting_shipped'])){
                    $flag = false;
                }
            }
            return $flag;
        }

        //混合单必须是单内每个产品都可以导出时才可以导出
        if($order->order_type == 'mixture'){
            $flag = true;
            foreach($order->items as $item){
                if(in_array($item->item_status,['shipped','cancelled'])){
                    continue;
                }
                if($item->item_type == 'custom' && !in_array($item->item_status,['waiting_shipped','waiting_accept'])){
                    $flag = false;
                }
                if($item->item_type == 'taobao' && !in_array($item->item_status,['waiting_shipped'])){
                    $flag = false;
                }
            }
            return $flag;
        }
    }

    public function actionExportUpsEds(){
        $posts = Yii::$app->request->post();
        if(!empty($posts['ids'])){
            $ids = explode(",",$posts['ids']);
            $orderModel = new Order();
            $orders = $orderModel->find()->with('items')->with('address')->where(['in','id',$ids])->andWhere(['shipping_method'=>'UPS','has_shipment'=>'1'])->distinct()->all();
            $header = $this->createExportUpsHeader();
            $data = $this->formatExportUpsData($orders);
            $objExportData = new ExportData($header,$data);
            $objExportData->createExcel($header,$data,20,'pre-alert',0,['mergeFirstColumn'=>false]);
            $path = 'download/shipment/UPS/'.date("Y-m-d").'/'.'UPS-pre-alert'.date('Y-m-d').'.csv';
            $objExportData->saveFileTo($path,'CSV',['enclosure'=>true]);
            return $this->json_output(['data'=>['/'.$path]]);

        }else{
            return $this->json_output(['status'=>0,'msg'=>'请选择要生成物流的运单']);
        }
    }

    /**
     * 导出EDS的头部
     * @return array
     */
    private function createExportUpsHeader(){
        $header = [
            '货件信息取消标记',
            '货件信息主追踪编号',
            '货件信息服务类型',
            '货件信息包裹数量',
            '货件信息实际重量',
            '发自UPS帐号',
            '发自公司或姓名',
            '发自地址行1',
            '发自经手人',
            '发自电话',
            '发至公司或姓名',
            '发至地址行1',
            '发至经手人',
            '发至电话',
            '发至国家地区',
            '货物货物描述',
            '货物发票CN22单位',
            '货物发票CN22计量单位',
            '货物发票EEICN22单价',
            '货物货币代码',
            '发至邮政编码',
            '货物发票NAFTACN22关税代码',
            '包裹追踪编号',
            '货件信息参考编号1',
        ];
        return $header;
    }

    /**
     * 格式化要导出的数据
     * @param $data
     * @return array
     */
    private function formatExportUpsData($orders){
        $output = [];
        foreach($orders as $index=>$order){
            $address = $order->address;
            $output[$index][] = 'N';
            $output[$index][] = $order->shipping_track_no;
            $output[$index][] = 'Express Saver';
            $output[$index][] = '1';
            $output[$index][] = '0.5';
            $output[$index][] = '1W508R';
            $output[$index][] = "GZ JEULIA JEWELLERY CO.,LTD";
            $output[$index][] = 'SHATOU STREET YINJIAN RD 123#';
            $output[$index][] = 'MR LIN';
            $output[$index][] = '18003152361';
            $output[$index][] = CommonHelper::filterEmptyStr($address->firstname).' '.CommonHelper::filterEmptyStr($address->lastname);
            $street = $address->street? str_replace(["\r\n", "\r", "\n"], ' ', $address->street) : '';
            $output[$index][] = str_replace(",",' ',$street);
            $output[$index][] = CommonHelper::filterEmptyStr($address->firstname).' '.CommonHelper::filterEmptyStr($address->lastname);
            $output[$index][] = CommonHelper::filterEmptyStr($address->telephone);
            $output[$index][] = $address->country_id;
            $output[$index][] = 'ALLOY RING';
            $output[$index][] = '1';
            $output[$index][] = 'PCS';
            $output[$index][] = '30';
            $output[$index][] = 'USD';
            $output[$index][] = $address->postcode;
            $output[$index][] = '';
            $output[$index][] = $order->shipping_track_no;
            $output[$index][] = $order->ext_order_id;
        }
        return $output;
    }

}