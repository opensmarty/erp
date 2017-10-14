<?php
/**
 * CustomConroller.php 
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/5/23
 */

namespace app\controllers\factory;


use app\controllers\BaseController;
use app\helpers\ItemStatus;
use app\helpers\Options;
use app\models\order\StockOrder;
use app\models\product\Product;
use app\models\product\Stock;
use renk\yiipal\components\ExportData;
use renk\yiipal\helpers\ArrayHelper;
use renk\yiipal\helpers\FileHelper;
use Yii;
use app\models\order\Item;

class StocksController extends BaseController{

    /**
     * 库存生产列表
     * @return string
     */
    public function actionStocks(){
        $searchModel = new StockOrder();
        $conditions = [
            Item::TASK_STATUS_WAITING_PRODUCTION,
            Item::TASK_STATUS_IN_PRODUCTION,
            Item::TASK_STATUS_WAIT_ACCEPT,
            Item::TASK_STATUS_WAITING_REPAIR,
            Item::TASK_STATUS_BEING_REPAIRED,
        ];

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams,null,[['item_type'=>'custom'],['in','item_status',$conditions]]);
        return $this->render('stocks', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * 修改补库存总数
     * @param $id
     * @return array
     */
    public function actionEditOrderNumber(){
        $id = $this->post('pk');
        $value = $this->post('value');
        $model = StockOrder::findOne($id);
        if($model && $model->item_status == 'waiting_production'){
            $model->qty_ordered = trim($value);
            $model->save();
            return $this->json_output();
        }else{
            return $this->json_output(['status'=>0,'msg'=>'处理失败']);
        }
    }


    /**
     * 库存采购列表
     * @return string
     */
    public function actionTaobao(){
        $searchModel = new StockOrder();
        $conditions = [
            Item::TASK_STATUS_PENDING_PURCHASE,
            Item::TASK_STATUS_PURCHASE,
        ];
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams,null,[['item_type'=>'taobao'],['in','item_status',$conditions]]);
        return $this->render('taobao', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }


    /**
     * 补库存生产列表(在工厂管理菜单中)
     * @return string
     */
    public function actionStockup(){
        $searchModel = new StockOrder();
        $conditions = [
            Item::TASK_STATUS_WAITING_PRODUCTION,
            Item::TASK_STATUS_IN_PRODUCTION,
            Item::TASK_STATUS_WAIT_ACCEPT,
            Item::TASK_STATUS_WAITING_REPAIR,
            Item::TASK_STATUS_BEING_REPAIRED,
        ];
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams,null,[['item_type'=>'custom'],['in','item_status',$conditions]]);
        return $this->render('stockup', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * 库存列表
     * @return string
     */
    public function actionStocksList(){
        $searchModel = new Product();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $models = $dataProvider->getModels();
        $productIds = [];
        foreach($models as $product){
            $productIds[] = $product->id;
        }
        $stocks = $searchModel->getProductsStocks($productIds);
        foreach($models as &$model){
            $model->stocksInfo = isset($stocks[$model->id])?$stocks[$model->id]:[];
        }

        $stockInfo['stocksTotal'] = Stock::find()->sum('total');
        $stockInfo['virtualTotal'] = StockOrder::find()->where(['not in','item_status',["purchase_completed","product_passed"]])->sum('qty_ordered-qty_passed');

        return $this->render('list', [
            'searchModel' => $searchModel,
            'stockInfo' => $stockInfo,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * 开始生产
     */
    public function actionStart(){
        $posts = Yii::$app->request->post();
        if(!empty($posts['ids'])){
            $ids = explode(",",$posts['ids']);
            $model = new StockOrder();
            $model->startProduce($ids);
            return $this->json_output();
        }else{
            return $this->json_output(['status'=>0,'msg'=>'请选择要生产的订单']);
        }
    }

    /**
     * 请求验收
     */
    public function actionRequestAccept($id){
        if($this->isPost()){
            $posts = Yii::$app->request->post();
            if($posts['number']>0){
                $model = new StockOrder();
                $model->requestAccept($id, $posts['number']);
                return $this->json_output();
            }else{
                return $this->json_output(['status'=>0,'msg'=>'数量必须大于0.']);
            }
        }else{
            $stockOrder = StockOrder::find()->where(['id'=>$id])->one();
            return $this->renderAjax('//fragment/request-accept',['modal'=>$stockOrder]);
        }

    }

    /**
     * 验收通过
     * @param $id
     */
    public function actionAcceptRequest($id){
        if($this->isPost()){
            $posts = Yii::$app->request->post();
            if($posts['number']>=0){
                $model = new StockOrder();
                $model->acceptRequest($id, $posts);
                return $this->json_output();
            }else{
                return $this->json_output(['status'=>0,'msg'=>'次品数量必须大于等于0.']);
            }
        }else{
            $stockOrder = StockOrder::find()->where(['id'=>$id])->one();
            return $this->renderAjax('//fragment/accept-request',['modal'=>$stockOrder]);
        }

    }

    /**
     * 等待验收数量
     * @param $id
     * @return array|string
     */
    public function actionEditAcceptNumber(){
        $id = $this->post('pk');
        $value = trim($this->post('value'));
        $value = $value>0?$value:0;
        $model = StockOrder::findOne($id);
        if($model){
            $qty_left = $model->qty_ordered-$model->qty_passed;
            $qty_left = $qty_left>0?$qty_left:0;
            $model->qty_delivery = $value>$qty_left?$qty_left:$value;
            $model->save();
            return $this->json_output();
        }else{
            return $this->json_output(['status'=>0,'msg'=>'处理失败']);
        }
    }

    /**
     * 采购完成
     * @return array
     */
    public function actionCompletePurchase(){
        $posts = Yii::$app->request->post();
        if(!empty($posts['ids'])) {
            $ids = explode(",", $posts['ids']);
            $model = new StockOrder();
            $model->completePurchase($ids);
            return $this->json_output();
        }else{
            return $this->json_output(['status'=>0,'msg'=>'请选择要采购的订单']);
        }
    }

    /**
     * 导出补库存生产产品
     */
    public function actionExportForStocksProduce(){
        $posts = Yii::$app->request->post();
        if(!empty($posts['ids'])) {
            $ids = explode(",", $posts['ids']);
            return $this->exportForStocksProduce($ids);
        }else{
            return $this->json_output(['status'=>0,'msg'=>'请选择要导出的订单']);
        }
    }



    /**
     * 导出产品数据
     * @param $ids
     * @return array
     */
    private function exportForStocksProduce($ids){
        $items = StockOrder::find()->with('product')->where(['in','stock_order_item.id',$ids])->all();
        $header = $this->createExportHeader();
        $data = $this->formatExportData($items);
        $objExportData = new ExportData($header,$data);
        $objExportData->createExcel($header,$data);
        $path = 'download/stocks/factory/'.date("Y-m-d").'/'.'products-'.date('Y-m-d-H-i-s').'.xls';
        $objExportData->saveFileTo($path);
        return $this->json_output(['data'=>['/'.$path]]);
    }


    /**
     * 配货单头部
     * @return array
     */
    private function createExportHeader(){
        $header = [
            '编号',
            '图片',
            'SKU',
            '版号',
            '数量',
            '款式',
            '尺码',
            '刻字',
            '当前状态',
            '开始时间',
            '结束时间',
        ];
        return $header;
    }

    /**
     * 格式化要导出的数据
     * @param $data
     * @return array
     */
    private function formatExportData($data){
        $output = [];
        foreach($data as $index=>$item){
            $output[$index][] = 'S-'.$item->ext_order_id;
            $file = $item->product->getMasterImage();
            $filePath = FileHelper::getThumbnailPath($file->file_path, '300x300');
            $filePath = str_replace(urlencode("#"),"#",$filePath);
            $output[$index][] = ['type'=>'image','value'=>'.'.$filePath];
            $output[$index][] = $item->sku;
            $output[$index][] = $item->product->template_no;
            $output[$index][] = $item->qty_ordered;
            $output[$index][] = Options::ringTypes($item->product_type);
            $output[$index][] = empty($item->size_us)?'':$item->size_us;
            $output[$index][] = '刻字: '.html_entity_decode($item->engravings);
            $output[$index][] = ItemStatus::allStatus($item->item_status);
            $output[$index][] = date("Y-m-d H:i:s",$item->created_at);
            $output[$index][] = date("Y-m-d H:i:s",$item->created_at+$item->getDeliveryTime());
        }
        return $output;
    }

    /**
     * 导出库存数据
     * @return array
     */
    public function actionExportStocks(){
        $posts = Yii::$app->request->post();
        if(!empty($posts['ids'])) {
            $ids = explode(",", $posts['ids']);
            return $this->exportProductStocks($ids);
        }else{
            return $this->json_output(['status'=>0,'msg'=>'请选择要导出的产品']);
        }
    }

    /**
     * 处理导出库存数据
     * @param $ids
     * @return array
     */
    private function exportProductStocks($ids){
        $product = new Product();
        $items = $product->getProductsStocks($ids);
        $header = $this->createExportStocksHeader();
        $data = $this->formatExportStocksData($items);
        $objExportData = new ExportData($header,$data);
        $objExportData->createExcel($header,$data,20);
        $path = 'download/stocks/factory/'.date("Y-m-d").'/'.'products-'.date('Y-m-d-H-i-s').'.xls';
        $objExportData->saveFileTo($path);
        return $this->json_output(['data'=>['/'.$path]]);
    }

    /**
     * 配货单头部
     * @return array
     */
    private function createExportStocksHeader(){
        $header = [
            'SKU',
            '图片',
            '尺码',
            '产品类型',
            '实际库存',
            '在补库存',
        ];
        return $header;
    }

    /**
     * 格式化要导出的数据
     * @param $data
     * @return array
     */
    private function formatExportStocksData($data){
        $output = [];
        $lastSku = '';
        $index = 0;
        foreach($data as $productId=>$row){
            $product = Product::findOne($productId);
            foreach($row as $item){
                $output[$index][] = $product->sku;
                $file = $product->getMasterImage();
                $filePath = FileHelper::getThumbnailPath($file->file_path, '300x300');
                $filePath = str_replace(urlencode("#"),"#",$filePath);
                if($lastSku != $product->sku){
                    $output[$index][] = ['type'=>'image','value'=>'.'.$filePath];
                }else{
                    $output[$index][] = '';
                }
                $output[$index][] = $item['size_code'];
                $output[$index][] = $item['type'];
                $output[$index][] = $item['actual_total'];
                $output[$index][] = $item['virtual_total'];
                $index++;
                $lastSku = $product->sku;
            }
        }
        return $output;
    }

}