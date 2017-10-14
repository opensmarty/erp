<?php
/**
 * Variable.php 
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/7/4
 */

namespace app\models;


class Variable extends BaseModel{

    /**
     * @inheritdoc
     */
    public static function primaryKey()
    {
        return ['name'];
    }

    /**
     * @inheritdoc
     */
    public function behaviors(){
        return [];
    }

    public static function get($name,$default=null){
        static $cache = [];
        if(empty($name)) return $default;
        if(isset($cache[$name])){
            return $cache[$name];
        }
        $data = self::find()->where(['name'=>$name])->one();
        if($data){
            $cache[$name] = $data->value;
        }else{
            $cache[$name] = $default;
        }
        return $cache[$name];
    }

    public static function set($name,$value){
        $object = self::find()->where(['name'=>$name])->one();
        if(empty($object)){
            $object = new Variable();
            $object->name = $name;
        }
        $object->value = $value;
        $object->save();
    }
}