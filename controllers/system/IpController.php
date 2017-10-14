<?php
/**
 * IpController.php 
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/11/8
 */

namespace app\controllers\system;


use app\controllers\BaseController;
use app\models\IpList;
use app\models\Variable;
use yii;

class IpController extends BaseController{

    /**
     * Ip列表
     * @return string
     */
    public function actionIndex(){
        $searchModel = new IpList();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $ipFilter = Variable::get("ip_filter_disabled",false);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'ipFilter' => $ipFilter,
        ]);
    }

    public function actionCreate(){
        $model = new IpList();
        if($this->post()){
            if($model->load($this->post()) && $model->save()){
                return $this->redirect(['index']);
            }
        }else{
            $ip = Yii::$app->getRequest()->getUserIP();
            return $this->render('update', [
                'model' => $model,
                'ip' => $ip,
            ]);
        }
    }

    public function actionDelete($id){
        $this->findModel(IpList::className(),$id)->delete();
        return $this->redirect(['index']);
    }

    public function actionUpdate($id){
        $model = IpList::findOne($id);
        if($this->post()){
            if($model->load($this->post()) && $model->save()){
                return $this->redirect(['index']);
            }
        }else{
            $ip = Yii::$app->getRequest()->getUserIP();
            return $this->render('update', [
                'model' => $model,
                'ip' => $ip,
            ]);
        }
    }

    public function actionDisabled(){
        $ipFilter = Variable::get("ip_filter_disabled",false);
        Variable::set("ip_filter_disabled",!$ipFilter);
        return $this->json_output();
    }
}