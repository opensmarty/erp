<?php
/**
 * AnalysicController.php 
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/11/1
 */

namespace app\controllers\shipment;


use app\controllers\BaseController;
use app\models\shipment\ShipmentFeeGroup;
use yii;

class FeeController extends BaseController{
    /**
     * 物流费用管理
     * @return string
     */
    public function actionIndex(){
        $searchModel = new ShipmentFeeGroup();
        $costInfo = $searchModel->getCostInfo();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'costInfo' => $costInfo,
        ]);
    }

    /**
     * 导入物流费用
     * @return string|yii\web\Response
     */
    public function actionImport(){
        $model = new ShipmentFeeGroup();
        if ($model->load(Yii::$app->request->post()) && $model->import()) {
            Yii::$app->session->setFlash('success', '操作成功！');
            return $this->redirect(['index']);
        }
        return $this->render('import-shipments-fee',[
            'model'=>$model
        ]);
    }
}