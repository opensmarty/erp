<?php
/**
 * CustomConroller.php 
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/5/23
 */

namespace app\controllers\distribution;


use app\controllers\BaseController;
use app\helpers\CommonHelper;
use app\helpers\ItemStatus;
use app\models\order\Item;
use app\models\order\Order;
use app\models\product\Product;
use renk\yiipal\components\ExportData;
use renk\yiipal\helpers\FileHelper;
use Yii;
class MixtureController extends BaseController{

    /**
     * 订单列表
     * @return string
     */
    public function actionMixture(){
        $searchModel = new Order();
        $mixtureConditions = [
            ['order_type'=>Order::ORDER_TYPE_MIXTURE],
            ['in','status',[
                Item::TASK_STATUS_PROCESSING,
                Item::TASK_STATUS_WAITING_SHIPPED,
            ]],
            ['not in','item_status',[
                Item::TASK_STATUS_SHIPPED,
                Item::TASK_STATUS_CANCELLED
            ]],
            ['order.approved'=>1],
            "order.blocked =0 OR (order.blocked=1 AND order.status<>'waiting_shipped')",
            ['<>','order.status','pending'],
            "order.paused = 0 OR order.paused = 2 OR (order.paused=1 AND order.status<>'waiting_shipped')",
            ['<>','order_item.item_status','shipped']
        ];
        $searchModel->leftJoinTables['order_item'] = 'order_item';
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams,null,$mixtureConditions);

        $models = $dataProvider->getModels();
        $productIds = [];
        foreach($models as $model){
            foreach ($model->items as $item) {
                $productIds[] = $item->product_id;
            }
        }
        $productModel = new Product();
        $productStocks = $productModel->getProductsStocks($productIds);

        foreach($models as &$model){
            $model->allStocks = $productStocks;
        }

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * 导出混合单
     * @return array
     */
    public function actionExport(){
        $posts = Yii::$app->request->post();
        if(!empty($posts['ids'])){
            $ids = explode(",",$posts['ids']);
            $items = Item::find()->with('product')->with('order')
                ->innerJoin('order','order_item.order_id = order.id')->where(['in','order_id', $ids])->orderBy('order_id DESC')->all();
            $header = $this->createExportHeader();

            $productIds = [];
            foreach($items as $model){
                $productIds[] = $model->product_id;
                if($model->item_status == Item::TASK_STATUS_PICK_WAITING){
                    $model->item_status = Item::TASK_STATUS_PICKING;
                    $model->save();
                }
            }
            $productModel = new Product();
            $productStocks = $productModel->getProductsStocks($productIds);
            foreach($items as &$model){
                $model->allStocks = $productStocks;
            }

            $data = $this->formatExportData($items);
            $objExportData = new ExportData($header,$data);
            $objExportData->createExcel($header,$data);
            $path = 'download/distribution/'.date("Y-m-d").'/'.'orders-'.date('Y-m-d-H-i-s').'.xls';
            $objExportData->saveFileTo($path);
            return $this->json_output(['data'=>['/'.$path]]);
        }else{
            return $this->json_output(['status'=>0,'msg'=>'请选择要导出的项目']);
        }
    }

    /**
     * 导出的头部
     * @return array
     */
    private function createExportHeader(){
        $header = [
            '编号',
            '订单号',
            '物流公司',
            'SKU',
            '图片',
            '网站尺码',
            '刻字',
            '配货数量',
            '库存数量',
            '处理状态',
        ];
        return $header;
    }

    /**
     * 格式化要导出的数据
     * @param $data
     * @return array
     */
    private function formatExportData($items){
        $productType = ['none'=>'','men'=>'(男)','women'=>'(女)'];
        $output = [];
        foreach($items as $index=>$item){
            if(!$item->canDistribute())continue;
            $output[$index][] = $item->order->ext_order_id;
            $output[$index][] = $item->order->increment_id;
            $output[$index][] = $item->order->shipping_method;
            $output[$index][] = $item->sku;
            $image = $item->product->getMasterImage();
            $filePath = FileHelper::getThumbnailPath($image->file_path, '300x300');
            $filePath = str_replace(urlencode("#"),"#",$filePath);
            $output[$index][] = ['type'=>'image','value'=>'./'.$filePath];
            $output[$index][] = $item->size_original.$productType[$item->size_type];
            $output[$index][] = '刻字: '.html_entity_decode($item->engravings).$productType[$item->engravings_type];
            $output[$index][] = $item->qty_ordered;
            $stocksInfo = isset($item->allStocks[$item->product_id])?$item->allStocks[$item->product_id]:[];
            $stocks = CommonHelper::getStocksBy($stocksInfo,$item->size_us,$item->size_type);
            $output[$index][] = $stocks['actual_total'].'('.intval($stocks['virtual_total']).')';
            $output[$index][] = ItemStatus::allStatus($item->item_status);
        }
        return $output;
    }
}