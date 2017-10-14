<?php

namespace app\models\product;

use app\models\BaseModel;
use app\models\File;
use yii\db\Query;
use yii\helpers\Url;

class ProductAttributes extends BaseModel
{

    public $product_cid = '';
    public $product_type = '';
    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = [
            [$this->getTableSchema()->getColumnNames(),'safe'],
            [['product_cid','product_type'],'safe'],
            [['stone_size','stone_carat','side_stone_number','width','thickness','weight','side_stone_size'],'required','when'=>function($model){
                if($model->product_cid == '3'){
                    return true;
                }else{
                    return false;
                }
            }],
            [['stone_2_size','stone_2_carat','side_stone_2_number','width_2','thickness_2','weight_2','side_stone_2_size'],'required','when'=>function($model){
                if($model->product_cid == '3' && ($model->product_type == 1 || $model->product_type == 2)){
                    return true;
                }else{
                    return false;
                }
            }],
            [['necklace_length','necklace_stone_number','necklace_pendant_height','necklace_pendant_width','necklace_stone_size'],'required','when'=>function($model){
                if($model->product_cid == '4'){
                    return true;
                }else{
                    return false;
                }
            }],
        ];
        return $rules;
    }
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'product_attributes';
    }

    public function attributeLabels()
    {
        return [
            'stone_type' => '主钻类型',
            'stone_2_type' => '主钻类型',
            'stone_color' => '主钻颜色',
            'stone_2_color' => '主钻颜色',
            'stone_size' => '主钻大小',
            'stone_2_size' => '主钻大小',
            'stone_carat' => '主钻克拉数',
            'stone_2_carat' => '主钻克拉数',
            'side_stone_number' => '边钻个数',
            'side_stone_2_number' => '边钻个数',
            'weight' => '戒指总重',
            'weight_2' => '戒指总重',
            'width' => '指环宽度',
            'width_2' => '指环宽度',
            'thickness' => '指环厚度',
            'thickness_2' => '指环厚度',
            'electroplating_color' => '电镀颜色',
            'electroplating_color_2' => '电镀颜色',
            'rings_number' => '套件数',
            'side_stone_size' => '边钻大小',
            'side_stone_2_size' => '边钻大小',
            'necklace_length' => '项链长度',
            'necklace_pendant_height' => '吊坠高度',
            'necklace_pendant_width' => '吊坠宽度',
            'necklace_stone_number' => '钻石数目',
            'necklace_stone_size' => '钻石大小',


        ];
    }
}
