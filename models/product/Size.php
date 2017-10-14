<?php

namespace app\models\product;

use app\models\BaseModel;
use app\models\File;

class Size extends BaseModel
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'size_alias';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = [
            [['size','alias'], 'required'],
            ['uid', 'default', 'value' => \Yii::$app->user->id],
        ];

        return $rules;
    }

    public function attributeLabels()
    {
        return [
            'size' => '尺码',
            'alias' => '别名',
        ];
    }

    /**
     * 获取尺码和别名
     * @return array
     */
    public static function getSizes(){
        static $output = [];
        if(!empty($output)){
            return $output;
        }
        $output = [];
        $sizes = self::find()->all();
        foreach($sizes as $size){
            $alias = $size->alias;
            $output[$size->size] = preg_split('/\r\n/',$alias);
        }
        return $output;
    }

    /**
     * 根据别名获取尺码.
     * @param $str
     * @return int|string
     */
    public static function getSizeByAlias($str){
        $sizes = self::getSizes();
        foreach($sizes as $size=>$alias){
            if(array_search(trim($str), $alias) !== false){
                return $size;
            }
        }
        return '';
    }
}
