<?php
/**
 * Order.php 
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/5/19
 */

namespace app\models\order;

use app\models\BaseModel;
use app\models\product\Product;
use app\models\product\Stock;
use app\models\User;
use renk\yiipal\helpers\Url;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

class OrderPaymentStatusTracking extends BaseModel{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'order_payment_status_tracking';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = [
            [$this->getTableSchema()->getColumnNames(),'safe'],
        ];
        return $rules;
    }


    /**
     * 记录订单付款状态变化
     * @param $order
     * @param string $description
     */
    public static function track($order){
        $order = Order::findOne(['id'=>$order->id]);
        $model = new OrderPaymentStatusTracking();
        $model->order_id = $order->id;
        $model->ext_order_id = $order->ext_order_id;
        $model->increment_id = $order->increment_id;
        $model->payment_status = $order->payment_status;
        $model->save();
    }
}