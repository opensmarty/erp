<?php
/**
 * MaterialController.php 
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/10/26
 */

namespace app\controllers\supplies;


use app\controllers\BaseController;
use app\models\supplies\Material;
use yii;
class MaterialController extends BaseController{
    /**
     * 耗材列表
     * @return string
     */
    public function actionIndex(){
        $searchModel = new Material();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * 添加耗材
     */
    public function actionCreate(){
        $model = new Material();
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        }else{
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * 编辑耗材
     * @param $id
     * @return string|yii\web\Response
     * @throws \app\controllers\NotFoundHttpException
     */
    public function actionUpdate($id){
        $model = $this->findModel(Material::className(),$id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * 删除耗材
     * @param $id
     * @return yii\web\Response
     */
    public function actionDelete($id){
        $model = Material::findOne($id);
        $model->delete();
        return $this->redirect(['index']);
    }
}