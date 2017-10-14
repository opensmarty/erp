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

use app\helpers\CommonHelper;
use app\models\BaseModel;
use app\models\order\Order;
use app\models\User;
use yii\web\UploadedFile;

class ShipmentLog extends BaseModel{

    //发货正常
    const SHIPMENT_STATUS_NORMAL = 'normal';
    //地址错误
    const SHIPMENT_STATUS_ADDRESS_WRONG = 'address_wrong';
    //产品错误
    const SHIPMENT_STATUS_PRODUCT_WRONG = 'product_wrong';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'shipment_log';
    }

    public function attributeLabels()
    {
        return [
            'ext_order_id' => '订单编号',
        ];
    }

    /**
     * 格式化查询参数
     * @param $params
     * @return array
     */
    protected function formatSearchParams($params){
        $output = [];
        foreach($params as $field=>$item){
            $item = trim($item);
            if($item == ''){
                continue;
            }
            switch($field){
                case 'ship_uid':
                case 'report_uid':

                    $user = User::find()->where(['nick_name'=>$item])->one();
                    if($user){
                        $output[] = [$field=>$user->id];
                    }else{
                        $output[] = [$field=>0];
                    }
                    break;
                default:
                    $output[] = [$field=>$item];
            }
        }
        return $output;
    }

    /**
     * 获取发货人信息
     * @return \yii\db\ActiveQuery
     */
    public function getShipmentUser(){
        return $this->hasOne(User::className(), ['id' => 'ship_uid']);
    }

    /**
     * 获取报告人信息
     * @return \yii\db\ActiveQuery
     */
    public function getReportUser(){
        return $this->hasOne(User::className(), ['id' => 'report_uid']);
    }

    /**
     * 获取我的发货统计
     */
    public function getMyStaticInfo($dateRange=false,$uid=false){
        $output = [];
        $query = self::find()->where("1");
        if($dateRange){
            $date = CommonHelper::splitDateRange($dateRange);
            $query->andWhere(['>','created_at',$date['start']]);
            $query->andWhere(['<=','created_at',$date['end']]);
        }

        if($uid){
            $query->andWhere(['ship_uid'=>$uid]);
        }
        $totalNumber = $query->count();


        $query = self::find()->where(['status'=>'address_wrong']);
        if($dateRange){
            $date = CommonHelper::splitDateRange($dateRange);
            $query->andWhere(['>','created_at',$date['start']]);
            $query->andWhere(['<=','created_at',$date['end']]);
        }
        if($uid){
            $query->andWhere(['ship_uid'=>$uid]);
        }
        $wrongNumber = $query->count();
        $output['total_number'] = $totalNumber;
        $output['wrong_number'] = $wrongNumber;
        if($totalNumber>0){
            $wrongRate = $wrongNumber/$totalNumber;
        }else{
            $wrongRate = 0;
        }

        $output['wrong_rate'] = round($wrongRate*100,2);
        return $output;
    }
}