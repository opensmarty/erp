<?php
/**
 * DistributionController.php 
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/7/19
 */

namespace app\controllers\dashboard;

use app\models\User;
use yii;
use app\controllers\BaseController;
use app\models\shipment\ShipmentLog;

class DistributionController extends BaseController{
    /**
     * 我的工作记录
     * @return string
     */
    public function actionIndex(){
        $params = $this->get('ShipmentLog',[]);
        $dateRange = false;
        if(isset($params['created_at'])){
            $dateRange = $params['created_at'];
        }
        $searchModel = new ShipmentLog();
        $uid = $this->getCurrentUid();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams,null,[['ship_uid'=>$uid]]);
        $staticInfo = $searchModel->getMyStaticInfo($dateRange,$uid);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'staticInfo'=>$staticInfo,
        ]);
    }


    /**
     * 查看错误原因
     * @param $id
     * @return string
     */
    public function actionNote($id){
        $model = ShipmentLog::findOne($id);
        return $this->renderAjax('note', [
            'model' => $model,
        ]);
    }

    /**
     * 管理员查看
     */
    public function actionReport(){
        $searchModel = new ShipmentLog();
        $params = $this->get('ShipmentLog',[]);
        $dateRange = false;
        $shipUid = false;
        if(isset($params['created_at'])){
            $dateRange = $params['created_at'];
        }
        if(isset($params['ship_uid']) && trim($params['ship_uid']) !=''){
            $shipUser = User::find()->where(['nick_name'=>trim($params['ship_uid'])])->one();
            if($shipUser){
                $shipUid = $shipUser->id;
            }else{
                $shipUid = -1;
            }
        }
        $uid = $this->getCurrentUid();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $staticInfo = $searchModel->getMyStaticInfo($dateRange,$shipUid);
        return $this->render('admin-index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'staticInfo'=>$staticInfo,
        ]);
    }
}