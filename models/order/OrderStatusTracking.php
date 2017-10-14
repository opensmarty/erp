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

class OrderStatusTracking extends BaseModel{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'order_status_tracking';
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

    public function getUser(){
        return $this->hasOne(User::className(), ['id' => 'uid']);
    }

    /**
     * 记录订单状态变化
     * @param $order
     * @param string $description
     */
    public static function track($order,$description = ''){
        $tracking = new OrderStatusTracking();
        $tracking->order_id = $order->id;
        $tracking->ext_order_id = $order->ext_order_id;
        $tracking->increment_id = $order->increment_id;
        $tracking->status = $order->status;
        $tracking->description = $description;
        $tracking->uid = self::getCurrentUid();
        $tracking->save();
    }
}