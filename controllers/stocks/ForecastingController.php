<?php
/**
 * ForecastingController.php 
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/7/5
 */

namespace app\controllers\stocks;


use app\controllers\BaseController;
use app\helpers\CommonHelper;
use app\models\order\Item;
use app\models\product\Forecasting;
use app\models\product\Product;
use app\models\product\Size;
use app\models\product\Stock;
use yii;

class ForecastingController extends BaseController{
    public function actionIndex(){
        $searchModel = new Forecasting();
        $gets = $this->get();
        if(!isset($gets['Forecasting']['date_end'])){
            $gets['Forecasting']['date_end'] = date("Y-m-d");
        }
        $dataProvider = $searchModel->search($gets);
        if($gets['Forecasting']['date_end']<date("Y-m-d")){
            return $this->render('history', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }else{
            $models = $dataProvider->getModels();
            $productIds = [];
            foreach($models as $item){
                $productIds[] = $item->product_id;
            }
            $product = new Product();
            $stocks = $product->getProductsStocksInfo($productIds);
            foreach($models as &$model){
                $model->stocksInfo = isset($stocks[$model->product_id])?$stocks[$model->product_id]:[];
            }
            return $this->render('index', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }
    }

    /**
     * 产品历史销量
     * @param $id
     * @return string
     */
    public function actionSalesHistory($id){
        $info = Forecasting::findOne($id);
        $dateRange = CommonHelper::getDateDiff($info->date_end,$info->date_start);
        $data = Product::getSalesHistory($info->product_id, $info->size,$info->size_type,$info->date_start,$info->date_end);
        $totalQty = 0;
        foreach($data as $item){
            $totalQty += $item['qty'];
        }
        return $this->render('sales-history', [
            'data' => json_encode($data),
            'totalQty' => $totalQty,
            'dateRange' => $dateRange,
            'forecasting'=>$info,
        ]);
    }

    /**
     * 根据预测补库存
     * @param $id
     * @return array|string
     */
    public function actionAddStocks($id){
        $forecasting = Forecasting::findOne($id);
        $product = Product::findOne($forecasting->product_id);
        if($this->isPost()){
            $sizeList = Size::find()->All();
            $stockModel = new Stock();
            if($this->post()){
                $stockModel->addStocks($product,$this->post(),$sizeList);
                return $this->json_output();
            }
        }else{
            $size = Size::find()->where(['size'=>$forecasting->size])->one();
            $sizeId = 0;
            if($size){
                $sizeId = $size->id;
            }
            return $this->renderAjax('add-stocks',['model'=>$forecasting,'product'=>$product,'sizeId'=>$sizeId]);
        }
    }
}