<?php
/**
 * Service.php 
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/8/4
 */

namespace app\models\service;


use app\models\BaseModel;
use app\models\User;

class ServiceIssueItem extends BaseModel{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'service_issue_item';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = [
            [$this->getTableSchema()->getColumnNames(),'safe'],
            ['uid', 'default', 'value' => \Yii::$app->user->id],
        ];
        return $rules;
    }

    public function attributeLabels()
    {
        return [
            'subject' => '处理简述',
            'content' => '处理详述',
        ];
    }

    public function getUser(){
        return $this->hasOne(User::className(),['id'=>'uid']);
    }
}