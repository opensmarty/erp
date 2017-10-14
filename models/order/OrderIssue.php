<?php
/**
 * OrderIssue.php 
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/8/23
 */

namespace app\models\order;


use app\models\BaseModel;
use app\models\User;

class OrderIssue extends BaseModel{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'order_issue';
    }

    public function getOrder(){
        return $this->hasOne(Order::className(),['id'=>'order_id']);
    }

    /**
     * 获取订单的地址信息
     * @return $this
     */
    public function getAddress(){
        return $this->hasOne(Address::className(), ['parent_id' => 'order_id'])
            ->where('address_type = :threshold', [':threshold' => 'shipping'])
            ;
    }


    /**
     * 获取产品的报告人信息
     * @return \yii\db\ActiveQuery
     */
    public function getReportUser(){
        return $this->hasOne(User::className(), ['id' => 'report_uid']);
    }

    /**
     * 获取产品的报告人信息
     * @return \yii\db\ActiveQuery
     */
    public function getSolvedUser(){
        return $this->hasOne(User::className(), ['id' => 'solved_uid']);
    }
}