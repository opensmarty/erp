<?php
/**
 * SalesController.php 
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/8/26
 */

namespace app\controllers\sales;


use app\controllers\BaseController;
use app\models\product\Product;
use app\models\sales\Sales;
use yii;
class SalesController extends BaseController{
    public function actionReport(){
        $searchModel = new Sales();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams,null,[['<>','order_item.item_status','cancelled'],['order.payment_status'=>'processing']]);
        $models = $dataProvider->getModels();
        $productIds = [];
        foreach($models as $item){
            $productIds[] = $item->product_id;
        }
        $productModel = new Product();
        $stocks = $productModel->getProductsStocks($productIds);
        $params = $this->get("Sales",[]);
        if(isset($params['created_at']) && !empty($params['created_at'])){
            $dateRange = explode("/", $params['created_at']);
            $start = strtotime($dateRange[0] ." 00:00:00");
            $end = strtotime($dateRange[1] ." 23:59:59");
        }else{
            $start  = false;
            $end  = false;
        }
        $sales = $searchModel->getSalesInfo($productIds,$start,$end);
        foreach($models as &$model){
            $model->stocksInfo = isset($stocks[$model->product_id])?$stocks[$model->product_id]:[];
            $model->salesInfo = isset($sales[$model->product_id])?$sales[$model->product_id]:[];
        }
        return $this->render('report', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
}