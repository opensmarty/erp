<?php
/**
 * Config.php 
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/7/4
 */

namespace app\controllers\system;


use app\controllers\BaseController;
use app\models\Variable;

class ConfigController extends BaseController{
    public function actionForecasting(){
        if($this->isPost()){
            foreach($this->post('config') as $name=>$value){
                Variable::set($name,$value);
            }
            return $this->redirect(['forecasting']);
        }else{
            return $this->render('forecasting',[
                'model'=>new Variable(),
            ]);
        }
    }
}