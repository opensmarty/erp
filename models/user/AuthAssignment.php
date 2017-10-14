<?php
/**
 * authAssignment.php 
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/8/30
 */

namespace app\models\user;


use app\models\BaseModel;

class AuthAssignment extends BaseModel{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'auth_assignment';
    }
}