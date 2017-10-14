<?php
/**
 * WebsiteController.php
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/5/20
 */
namespace app\controllers;
use app\controllers\BaseController;
use app\models\order\Order;
use app\models\Website;
use yii;

class WebsiteController extends BaseController{

    public function actionIndex(){
        $searchModel = new Website();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionCreate(){
        $model = new Website();
        if($this->post()){
            if($model->load($this->post()) && $model->save()){
                return $this->redirect(['index']);
            }
        }else{
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    public function actionUpdate($id){
        $model = Website::findOne($id);
        if($this->post()){
            if($model->load($this->post()) && $model->save()){
                return $this->redirect(['index']);
            }
        }else{
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * 删除网站.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel(Website::className(),$id)->delete();

        return $this->redirect(['index']);
    }
}