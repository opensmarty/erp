<?php
/**
 * ShipmentFeeItem.php 
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/11/1
 */

namespace app\models\shipment;


use app\models\BaseModel;

class ShipmentFeeItem extends BaseModel{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'shipment_fee_item';
    }
}