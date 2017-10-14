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


class AjaxHelper {
    public static function json_output($data){
        $output = [
            'status'=>'00',
            'data' => [],
            'msg' => '操作成功!'
        ];
        $output = array_merge($output,$data);
        echo json_encode($output);exit;
    }
}