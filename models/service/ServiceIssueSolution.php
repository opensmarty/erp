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
use renk\yiipal\helpers\Url;

class ServiceIssueSolution extends BaseModel{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'service_issue_solution';
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

    /**
     * 列表页按钮组
     * @return array
     */
    public function buttons(){
        return $this->getItemButtons();
    }

    /**
     * 列表页按钮过滤
     * @return array
     */
    private function getItemButtons()
    {
        $buttons = [];
        $buttons[] = [
            'label' => '查看',
            'url' => Url::toRoute(['solution-view', 'id' => $this->id]),
            'icon' => 'glyphicon glyphicon-wrench',
            'attr' => ['class' => 'btn-solution-view'],
        ];


        $buttons[] = [
            'label' => '编辑',
            'url' => Url::toRoute(['solution-update', 'id' => $this->id]),
            'icon' => 'glyphicon glyphicon-wrench',
            'attr' => ['class' => 'btn-service-issue-update'],
        ];


        $buttons[] = [
            'label' => '删除',
            'url' => Url::toRoute(['solution-delete', 'id' => $this->id]),
            'icon' => 'glyphicon glyphicon-wrench',
            'attr' => ['class' => 'btn-service-issue-update confirm'],
        ];

        return $buttons;
    }

    public function attributeLabels()
    {
        return [
            'subject' => '标题',
            'content' => '处置方案',
        ];
    }

    public function getUser(){
        return $this->hasOne(User::className(),['id'=>'uid']);
    }
}