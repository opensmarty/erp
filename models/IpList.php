<?php
/**
 * IpList.php 
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/11/7
 */

namespace app\models;


class IpList extends BaseModel{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ip_list';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = parent::rules();
        $rules[] = ['uid', 'default', 'value' => \Yii::$app->user->id];
        return $rules;
    }

    /**
     * 设置Label
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'ip' => 'IP地址',
            'type' => '用途',
            'permission' => '状态',
            'updated_at' => '更新时间',
            'uid' => '设置人',
        ];
    }

    /**
     * 获取用户信息
     * @return \yii\db\ActiveQuery
     */
    public function getUser(){
        return $this->hasOne(User::className(),['id'=>'uid']);
    }
}