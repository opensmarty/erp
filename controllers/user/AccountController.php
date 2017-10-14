<?php
/**
 * CustomConroller.php 
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/5/23
 */

namespace app\controllers\user;


use app\controllers\BaseController;
use app\models\User;
use Yii;
class AccountController extends BaseController{

    /**
     * 账户编辑
     * @return string
     */
    public function actionUpdate(){
        $user = User::findOne($this->getCurrentUid());
        if($this->isPost()){
            $posts = $this->post();
            $user->nick_name = $posts['User']['nick_name'];
            if($password = trim($posts['User']['new_password'])){
                $user->password_hash = Yii::$app->security->generatePasswordHash($password);
            }
            $user->save();
            $this->setFlash();
        }
        return $this->render('update', [
            'model' => $user,
        ]);
    }
}