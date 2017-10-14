<?php
/**
 * authItem.php 
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/8/30
 */

namespace app\models\user;


use app\models\BaseModel;

class AuthItem extends BaseModel{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'auth_item';
    }

    public static function getRoles(){
        return self::findAll(['type'=>'1']);
    }
}