<?php
/**
 * PackingController.php 
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/10/26
 */

namespace app\controllers\supplies;


use app\controllers\BaseController;
use app\models\supplies\Material;
use app\models\supplies\Packing;
use app\models\supplies\PackingGroup;
use renk\yiipal\helpers\ArrayHelper;
use yii;

class PackingController extends BaseController{

    /**
     * 耗材进货管理
     * @return string
     */
    public function actionIndex(){
        $searchModel = new PackingGroup();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $material = Material::find()->asArray()->all();
        $materialOptions = ArrayHelper::options($material,'id','name');
//        $query = $this->get('Packing');
        $costInfo = $searchModel->getCostInfo(false);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'materialOptions' => $materialOptions,
            'costInfo' => $costInfo,
        ]);
    }

    /**
     * 耗材进货
     */
    public function actionCreate(){
        $model = new Packing();
        $posts = $this->post();

        if(isset($posts['Packing'])){
            $data = $posts['Packing'];
            $paid = $posts['paid'];
            $packingGroup = new PackingGroup();
            $groupId = $packingGroup->createGroup($paid);
            foreach($data['material_id'] as $index => $value){
                $material = Material::findOne($data['material_id'][$index]);
                $packing = new Packing();
                $packing->name = $material->name;
                $packing->material_id =$data['material_id'][$index];
                $packing->price =$data['price'][$index];
                $packing->qty =$data['qty'][$index];
                $packing->group_id = $groupId;
                $packing->save();
            }
            return $this->redirect(['index']);
        }else{
            $material = Material::find()->asArray()->all();
            $materialOptions = ArrayHelper::options($material,'id','name');
            $materialPriceOptions = ArrayHelper::options($material,'id','price');
            return $this->render('update', [
                'model' => $model,
                'materialOptions' => $materialOptions,
                'materialPriceOptions' => $materialPriceOptions,
            ]);
        }
    }

    /**
     * 编辑进货
     * @param $id
     * @return string|yii\web\Response
     * @throws \app\controllers\NotFoundHttpException
     */
    public function actionUpdate($id){
        $model = $this->findModel(Packing::className(),$id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        } else {
            $material = Material::find()->asArray()->all();
            $materialOptions = ArrayHelper::options($material,'id','name');
            $materialPriceOptions = ArrayHelper::options($material,'id','price');
            return $this->render('update-item', [
                'model' => $model,
                'materialOptions' => $materialOptions,
                'materialPriceOptions' => $materialPriceOptions,
            ]);
        }
    }

    /**
     * 交付耗材
     * @param $id
     * @return array|string
     */
    public function actionDelivered($id){
        if($this->isPost()){
            $posts = $this->post();
            $qty = $posts['qty'];
            if($qty>=0){
                $model = Packing::findOne($id);
                $number = $model->qty-$model->qty_delivered;
                $qty = $qty>$number?$number:$qty;
                $model->qty_delivered += $qty;
                $model->save();
                $model->checkFinished();

                $material = Material::findOne($model->material_id);
                $material->quantity += $qty;
                $material->save();
                return $this->json_output();
            }else{
                return $this->json_output(['status'=>0,'msg'=>'验收数量必须大于等于0.']);
            }
        }else{
            $model = Packing::find()->where(['id'=>$id])->one();
            return $this->renderAjax('delivered-ajax',['model'=>$model]);
        }
    }
}