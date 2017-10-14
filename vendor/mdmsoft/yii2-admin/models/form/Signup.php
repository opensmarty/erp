<?php
namespace mdm\admin\models\form;

use app\models\User;
use Yii;
use yii\base\Model;

/**
 * Signup form
 */
class Signup extends Model
{
    public $username;
    public $nick_name;
    public $email;
    public $password;
    public $websites;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['username', 'filter', 'filter' => 'trim'],
            ['websites','safe'],
            ['username', 'required'],
            ['username', 'unique', 'targetClass' => 'mdm\admin\models\User', 'message' => 'This username has already been taken.'],
            ['username', 'string', 'min' => 2, 'max' => 255],
            ['nick_name','required'],
            ['email', 'filter', 'filter' => 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'unique', 'targetClass' => 'mdm\admin\models\User', 'message' => 'This email address has already been taken.'],

            ['password', 'required'],
            ['password', 'string', 'min' => 6],
        ];
    }

    /**
     * Signs user up.
     *
     * @return User|null the saved model or null if saving fails
     */
    public function signup()
    {
        if ($this->validate()) {
            $user = new User();
            $user->username = $this->username;
            $user->nick_name = $this->nick_name;
            $user->email = $this->email;
            $user->setPassword($this->password);
            $user->generateAuthKey();
            if($this->websites){
                $user->websites = implode(",", $this->websites);
            }else{
                $user->websites = '-1';
            }
            if ($user->save()) {
                return $user;
            }
        }

        return null;
    }

    public function attributeLabels()
    {
        return [
            'username' => '用户名',
            'nick_name' => '姓名',
            'password' => '密码',
            'email' => '邮箱',
            'websites' => '网站',
        ];
    }

}
