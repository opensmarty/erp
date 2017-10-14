<?php

namespace app\models;

use app\models\BaseModel;
use app\models\File;

class Comment extends BaseModel
{

    //修改收货地址
    const COMMENT_TYPE_CHANGE_ADDRESS = 'change_address';
    //修改产品
    const COMMENT_TYPE_CHANGE_PRODUCT = 'change_product';
    //修改产品数量
    const COMMENT_TYPE_CHANGE_PRODUCT_NUMBER = 'change_product_number';
    //修改产品尺码
    const COMMENT_TYPE_CHANGE_PRODUCT_SIZE = 'change_product_size';
    //修改产品刻字
    const COMMENT_TYPE_CHANGE_PRODUCT_ENGRAVINGS = 'change_product_engravings';
    //修改快递方式
    const COMMENT_TYPE_CHANGE_SHIPPING_METHOD = 'change_shipping_method';
    //订单待定
    const COMMENT_TYPE_ORDER_PAUSE = 'order_pause';
    //订单取消
    const COMMENT_TYPE_ORDER_CANCEL = 'order_cancel';
    //订单加急
    const COMMENT_TYPE_ORDER_EXPEDITE = 'order_expedite';
    //修改订单总价
    const COMMENT_TYPE_CHANGE_GRAND_PRICE = 'change_grand_price';
    //退货换货
    const COMMENT_TYPE_ORDER_RETURN_EXCHANGE = 'order_return_exchange';
    //发货错误
    const COMMENT_TYPE_ORDER_SHIPMENT_WRONG = 'order_shipment_wrong';
    //其他
    const COMMENT_TYPE_OTHERS = 'others';

    //common
    const COMMENT_TYPE_EXPEDITE = 'expedite';
    const COMMENT_TYPE_CANCEL = 'cancel';
    const COMMENT_TYPE_DESCRIPTION = 'description';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'comment';
    }


    public function rules()
    {
        $rules = [
            [$this->getTableSchema()->getColumnNames(),'safe'],
            [['target_id','subject','content','visible_uids'],'required'],
        ];
        return $rules;
    }

    public function getUser(){
        return $this->hasOne(User::className(), ['id' => 'uid'])
            ;
    }

    public function save($runValidation = true, $attributeNames = null){
        if(empty($this->uid)){
            $this->uid = \Yii::$app->user->id;
        }
        $visibleUids = array_unique(explode(',',$this->visible_uids));
        $this->visible_uids = implode(',',$visibleUids);
        parent::save();
    }
}
