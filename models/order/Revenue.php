<?php
/**
 * Revenue.php 
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/7/18
 */

namespace app\models\order;


use app\models\BaseModel;
use yii\db\Expression;

class Revenue extends BaseModel{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'order';
    }

    public function getRevenue($startTime,$endTime,$paymentMethod,$client,$source){
        $query = Order::find()
                    ->where(['payment_status'=>'processing'])
                    ->andWhere(['not in','status',['cancelled','return_completed']])
                    ->andWhere(['>','created_at',$startTime])
                    ->andWhere(['<','created_at',$endTime])
                    ->groupBy('currency_code')
                    ->indexBy('currency_code')
                    ->select(['currency_code',new Expression('sum(grand_total) as total')])
                    ;
        if(!empty($paymentMethod)){
            $query->andWhere(['payment_method'=>$paymentMethod]);
        }
        if(!empty($client)){
            $query->andWhere(['from'=>$client]);
        }
        if(!empty($source)){
            $query->andWhere(['source'=>$source]);
        }
        $results = $query->asArray()->all();
        return $results;
    }
}