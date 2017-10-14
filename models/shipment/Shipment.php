<?php
/**
 * Order.php 
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

class Shipment extends BaseModel{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'shipment';
    }

    /**
     * 导入物流单号
     * @param bool $runValidation
     * @param null $attributeNames
     */
    public function import(){
        $files = UploadedFile::getInstances($this, 'files');

        if(empty($files)){
            $this->addError('files', '请上传CSV文件!');
            return false;
        }

        $file = $files[0];

        if (($handle = fopen($file->tempName, "r")) === FALSE) {
            $this->addError('files', '导入失败');
            return false;
        }

        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if( !$data ) {
                continue;
            }

            $order = Order::find()->where(['ext_order_id'=>$data[0]])->one();
            if( $order ) {

                $shipment = Shipment::find()->where(['order_id'=>$order->id])->one();
                if(empty($shipment)){
                    $shipment = new Shipment();
                    $shipment->order_id = $order->id;
                }

                if( isset($data[1]) && !empty($data[1]) ) {
                    $shipment->shipping_method = $data[1];
                }

                if( isset($data[2]) && !empty($data[2]) ) {
                    $shipment->shipping_label = $data[2];
                }

                if( isset($data[3]) && !empty($data[3]) ) {
                    $shipment->shipping_number = $data[3];
                }

                if( isset($data[4]) && !empty($data[4]) ) {
                    $shipment->shipping_weight = $data[4];
                }

                if( isset($data[5]) && !empty($data[5]) ) {
                    $shipment->shipping_price = $data[5];
                }
                $shipment->uid = \Yii::$app->user->id;
                $shipment->save();
                $order->shipping_method = $shipment->shipping_method;
                $order->shipping_track_no  = $shipment->shipping_number;
                $order->has_shipment = 1;
                $order->save();
            }
        }
        fclose($handle);
        return true;
    }
}