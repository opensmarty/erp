<?php
/**
 * ServiceOrder.php 
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/10/24
 */

namespace app\models\order;


use app\models\BaseModel;
use app\models\User;
use yii\db\Expression;

class ServiceOrder extends BaseModel{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'service_orders';
    }

    /**
     * 分发订单给客服
     * @param $orderId
     */
    public function dispatchOrder($orderId){
        $serviceOrder = ServiceOrder::find()->where(['order_id'=>$orderId])->one();
        if($serviceOrder){
            return true;
        }
        $order = Order::findOne($orderId);
        $serviceOrder = new ServiceOrder();
        $serviceOrder->order_id = $order->id;
        $serviceOrder->ext_order_id = $order->ext_order_id;
        $serviceOrder->increment_id = $order->increment_id;
        $serviceOrder->uid = $this->getAssignedUid();
        $serviceOrder->customer_email = $order->address->email;
        $serviceOrder->save();
        $order->service_id = $serviceOrder->uid;
        $order->save();
    }

    /**
     * 获取要分配的uid.
     * @return mixed
     */
    public function getAssignedUid(){
        $users = $this->getServiceUsers();
        $uids = array_keys($users);
        $serviceIds = ServiceOrder::find()->select('uid')->where(['>','created_at',strtotime(date("Y-m-d"))])->distinct()->indexBy('uid')->asArray()->all();
        $serviceIds = array_keys($serviceIds);
        $diff = array_diff($uids,$serviceIds);
        if(empty($diff)){
            $serviceOrder = ServiceOrder::find()->orderBy(new Expression("count(*) ASC"))->groupBy("uid")->where(['>','created_at',strtotime(date("Y-m-d"))])->one();
            return $serviceOrder['uid'];
        }else{
            return array_rand($diff);
        }
    }

    /**
     * 获取客服人员.
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getServiceUsers($website='it'){
        $users =  User::find()
                    ->join('LEFT JOIN','auth_assignment','auth_assignment.user_id = id')
                    ->where(['auth_assignment.item_name'=>'service'])
                    ->andWhere(['status'=>User::STATUS_ACTIVE])
                    ->andWhere('FIND_IN_SET(:threshold, websites)', [':threshold' => $website])
                    ->indexBy('id')->asArray()->all();
        return $users;
    }
}