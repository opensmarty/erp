<?php
/**
 * ShipmentReturn.php
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/5/19
 */

namespace app\models\shipment;

use app\models\BaseModel;
use app\models\order\Order;
use yii\web\UploadedFile;

class ShipmentReturn extends BaseModel{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'shipment_return';
    }
}