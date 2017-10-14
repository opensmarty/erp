<?php
/**
 * RefundController.php 
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/8/24
 */

namespace app\controllers\order;


use app\controllers\BaseController;
use app\models\Category;
use app\models\order\Order;
use app\models\order\OrderRefund;
use app\models\product\Product;
use yii\helpers\ArrayHelper;
use yii;

class RefundController extends BaseController{
    public function actionIndex(){
        $searchModel = new OrderRefund();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams,null,[['in','order_refund.refund_status',['pending','solved','processing']]]);
        $models = $dataProvider->getModels();
        $productIds = [];
        foreach($models as $model){
            foreach ($model->items as $item) {
                $productIds[] = $item->product_id;
            }
        }
        $products = Product::find()->where(['in','id',$productIds])->all();
        $products = ArrayHelper::index($products, 'id');
        $categories = Category::find()->indexBy('id')->asArray()->all();
        foreach($models as &$model){
            $model->products = $products;
            $model->categories = $categories;
        }
        $gets = $this->get('OrderRefund',[]);
        $tags = isset($gets['refund_tags'])?$gets['refund_tags']:'';
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'tags' => $tags,
        ]);
    }

    /**
     * 申请退款
     * @param $id
     */
    public function actionCreate($id){
        $order = Order::findOne($id);
        if(empty($order)){
            return $this->json_output(['status'=>0,'msg'=>'处理失败']);
        }
        $orderRefund = OrderRefund::findOne(['order_id'=>$order->id,'refund_status'=>'pending']);
        if($orderRefund){
            return '<div class="modal-body">此单已经有退款申请进行中，处理完后可再次申请。</div>';
        }
        $orderRefund = new OrderRefund();
        if($this->isPost()){
            $orderRefund->order_id = $order->id;
            $orderRefund->ext_order_id = $order->ext_order_id;
            $orderRefund->increment_id = $order->increment_id;
            $orderRefund->refund_tags = $this->post("refund_tags","");
            $orderRefund->total = $this->post("total",0);
            $orderRefund->report_uid = $this->getCurrentUid();
            $orderRefund->save();
            return $this->json_output(['command' => ['method'=>'redirect','url'=>'/order/refund/index']]);
        }
        return $this->renderAjax('//fragment/order-request-refund',['modal'=>$orderRefund,'order'=>$order]);
    }

    /**
     * 处理退款
     * @param $id
     * @return array
     */
    public function actionSolved($id){
        $orderRefund = OrderRefund::findOne($id);
        $orderRefund->refund_status = 'solved';
        $orderRefund->solved_uid = $this->getCurrentUid();
        $orderRefund->solved_at = time();
        $orderRefund->save();
        $order = Order::findOne($orderRefund->order_id);
        //扣减订单金额
        $order->grand_total = ($order->grand_total-$orderRefund->total);
        $order->save();
        return $this->json_output();
    }

    /**
     * 修改退款金额
     * @param $id
     * @return array
     */
    public function actionEditRefundPrice(){
        $id = $this->post('pk');
        $value = $this->post('value');
        $model = OrderRefund::findOne($id);
        if($model){
            $model->total = trim($value);
            $model->save();
            return $this->json_output();
        }else{
            return $this->json_output(['status'=>0,'msg'=>'处理失败']);
        }
    }
}