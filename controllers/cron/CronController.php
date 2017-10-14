<?php
/**
 * CronController.php 
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/6/24
 */
namespace app\controllers\cron;

use app\controllers\BaseController;
use app\models\Cron;
use yii;

class CronController extends BaseController{
    public function actionIndex(){
        $searchModel = new Cron();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionCreate(){
        $model = new Cron();
        if ($model->load(Yii::$app->request->post())) {
            $model->uid = $this->getCurrentUid();
            $model->save();
            return $this->redirect(['index']);
        }else{
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    public function actionUpdate($id){
        $model = $this->findModel(Cron::className(),$id);
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }
}