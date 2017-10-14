<?php
/**
 * ArrayHelper.php 
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/5/23
 */

namespace renk\yiipal\helpers;


class ArrayHelper extends \yii\helpers\ArrayHelper{
    public static function index($array, $key, $groups = [])
    {
        if(is_array($array)){
            return parent::index($array, $key, $groups);
        }

        $result = [];
        $groups = (array)$groups;

        foreach ($array as $element) {
            $lastArray = &$result;

            foreach ($groups as $group) {
                $value = static::getValue($element, $group);
                if (!array_key_exists($value, $lastArray)) {
                    $lastArray[$value] = [];
                }
                $lastArray = &$lastArray[$value];
            }

            if ($key === null) {
                if (!empty($groups)) {
                    $lastArray[] = $element;
                }
            } else {
                $value = static::getValue($element, $key);
                if ($value !== null) {
                    $lastArray[$value] = $element;
                }
            }
            unset($lastArray);
        }

        return $result;
    }

    public static function options($array, $key, $field)
    {
        $result = [];
        foreach ($array as $element) {
            $value = static::getValue($element, $key);
            if ($value !== null) {
                $result[$value] = static::getValue($element, $field);
            }
        }
        return $result;
    }
}