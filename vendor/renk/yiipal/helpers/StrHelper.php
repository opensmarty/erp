<?php
/**
 * AjaxHelper.php 
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/5/18
 */

namespace renk\yiipal\helpers;


class StrHelper {

    /**
     * 过滤空的字符串
     * @param $str
     * @param string $default
     * @return string
     */
    public static function getStr($str,$default=''){
        if(empty($str)){
            return $default;
        }else{
            return $str;
        }
    }
}