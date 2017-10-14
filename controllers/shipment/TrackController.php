<?php
/**
 * TrackController.php 
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/7/1
 */

namespace app\controllers\shipment;


use app\controllers\BaseController;
use app\models\order\Order;
use renk\yiipal\tracker\ShipmentTracker;

class TrackController extends BaseController{
    public function actionRealTimeTracking($id){
        $order = Order::findOne($id);
        if($order){
            $shippingMethod = $order->shipping_method;
            $trackNo = $order->shipping_track_no;
            $tracker = ShipmentTracker::get($shippingMethod);
            $data = $tracker->getInfoTable($trackNo);
            return $this->renderAjax('index',[
                'shippingMethod'=>$shippingMethod,
                'trackNo'=>$trackNo,
                'data'=> $data
            ]);
        }

    }
}