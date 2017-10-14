<?php
/**
 * Packing.php 
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/10/26
 */

namespace app\models\supplies;


use app\models\BaseModel;
use yii\helpers\Url;

class Packing extends BaseModel{
    public function attributeLabels()
    {
        return [
            'group_id' => '批次',
            'files' => '图片',
            'material_id' => '名称',
            'price' => '单价',
            'qty' => '数量',
            'qty_delivered' => '交付数量',
        ];
    }


    /**
     * 获取批次订单.
     * @return \yii\db\ActiveQuery
     */
    public function getPackingGroup(){
        return $this->hasOne(PackingGroup::className(),['id'=>'group_id']);
    }

    /**
     * 耗材
     * @return \yii\db\ActiveQuery
     */
    public function getMaterial(){
        return $this->hasOne(Material::className(),['id'=>'material_id']);
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
    private function getItemButtons(){
        $buttons = [];
        $buttons[] = [
            'label'=>'验收',
            'url'=>Url::toRoute(['/supplies/packing/delivered','id'=>$this->id]),
            'icon'=>'glyphicon glyphicon-wrench',
            'attr' =>['class'=>'btn-delivered ajax-modal'],
        ];

        $buttons[] = [
            'label'=>'编辑',
            'url'=>Url::toRoute(['update','id'=>$this->id]),
            'icon'=>'',
            'attr' =>'',
        ];
        return $buttons;
    }

    /**
     * 检车批次订单是否完成.
     */
    public function checkFinished(){
        $group = $this->packingGroup;
        $finished = true;
        $totalMoney = 0;
        foreach($group->packing as $item){
            if(($item->qty-$item->qty_delivered)>0){
                $finished = false;
                break;
            }
            $totalMoney+= round($item->price*$item->qty_delivered,2);
        }

        if($finished){
            $group->status = 'finished';
            $group->paid = $totalMoney;
            $group->save();
        }
    }
}