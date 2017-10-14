<?php
/**
 * OrderController.php 
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/10/24
 */

namespace app\controllers\dashboard;


use app\controllers\BaseController;
use app\models\order\Order;
use app\models\order\ServiceOrder;
use app\models\product\Product;
use renk\yiipal\helpers\ArrayHelper;
use yii;

class OrderController extends BaseController{
    public function actionIndex(){

//        $serviceOrder = new ServiceOrder();
//        $res = $serviceOrder->getAssignedUid();

        $uid = $this->getCurrentUid();
        $searchModel = new Order();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams,null,[['approved'=>1],['service_id'=>$uid]]);
        $models = $dataProvider->getModels();
        $productIds = [];
        foreach($models as $model){
            foreach ($model->items as $item) {
                $productIds[] = $item->product_id;
            }
        }
        $products = Product::find()->where(['in','id',$productIds])->all();
        $products = ArrayHelper::index($products, 'id');

        foreach($models as &$model){
            $model->products = $products;
        }

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
}