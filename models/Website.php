<?php
/**
 * Webiste.php
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/5/19
 */

namespace app\models;

use app\models\BaseModel;
use renk\yiipal\helpers\Url;
use yii\data\ActiveDataProvider;

class Website extends BaseModel{

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = [
            [['name','url','country','security_key','sender_email','service_email'],'required'],
        ];

        return $rules;
    }

    /**
     * 设置Label
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'name' => '名称',
            'country' => '国家简码',
            'url' => '网址',
            'security_key' => '密钥',
            'sender_email' => '发送邮箱',
            'service_email' => '客服邮箱',
        ];
    }

    /**
     * 列表页按钮组
     * @return array
     */
    public function buttons(){
        return $this->getButtons();
    }

    /**
     * 列表页按钮过滤
     * @return array
     */
    private function getButtons()
    {
        $buttons = [];
        $buttons[] = [
            'label' => '编辑',
            'url' => Url::toRoute(['update', 'id' => $this->id]),
            'icon' => 'glyphicon glyphicon-wrench',
            'attr' => ['class' => 'btn-website-update'],
        ];
        $buttons[] = [
            'label' => '删除',
            'url' => Url::toRoute(['delete', 'id' => $this->id]),
            'icon' => 'glyphicon glyphicon-wrench',
            'attr' => ['class' => 'btn-website-delete confirm'],
        ];
        return $buttons;
    }
}