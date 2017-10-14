<?php
/**
 * ConfirmController.php 
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/11/4
 */

namespace app\controllers\factory;


use app\controllers\BaseController;
use app\helpers\ItemStatus;
use app\helpers\Options;
use app\models\order\Item;
use app\models\order\Order;
use app\models\product\Product;
use renk\yiipal\components\ExportData;
use renk\yiipal\helpers\FileHelper;
use yii\helpers\ArrayHelper;
use \yii;

class ConfirmController extends BaseController{
    /**
     * 工厂变更确认列表
     * @return string
     */
    public function actionIndex(){
        $searchModel = new Order();
        $searchModel->leftJoinTables['order_item'] = 'order_item';
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams,null,[['in','order_item.factory_change_confirmed_status',['pending','solved']]]);
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

    /**
     * 工厂变更确认
     * @return array
     */
    public function actionChangeConfirm(){
        $posts = Yii::$app->request->post();
        if(!empty($posts['ids'])){
            $ids = explode(",",$posts['ids']);
            $orderModel = new Order();
            $orderModel->factoryChangeConform($ids);
            return $this->json_output();
        }else{
            return $this->json_output(['status'=>0,'msg'=>'请选择要确认的项目']);
        }
    }

    /**
     * 导出要变更确认的订单.
     * @return array
     */
    public function actionExport(){
        $posts = Yii::$app->request->post();
        if(!empty($posts['ids'])){
            $ids = explode(",",$posts['ids']);
            $items = Item::find()->with('order')->where(['in','order_id',$ids])->all();
            $header = $this->createExportHeader();
            $data = $this->formatExportData($items);
            $objExportData = new ExportData($header,$data);
            $objExportData->createExcel($header,$data);
            $path = 'download/factory/product/change/'.date("Y-m-d").'/'.'orders-'.date('Y-m-d-H-i-s').'.xls';
            $objExportData->saveFileTo($path);
            return $this->json_output(['data'=>['/'.$path]]);
        }else{
            return $this->json_output(['status'=>0,'msg'=>'请选择要导出的订单']);
        }
    }

    /**
     * 导出的头部
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
            '旧【尺码】',
            '新【尺码】',
            '旧【刻字】',
            '新【刻字】',
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
        $productIds = array_keys(ArrayHelper::index($data,'product_id'));
        $products = Product::find()->where(['in','id',$productIds])->all();
        $products = ArrayHelper::index($products,'id');
        $output = [];
        foreach($data as $index=>$item){
            $output[$index][] = $item->order->ext_order_id;
            $image = $products[$item->product_id]->getMasterImage();
            $filePath = FileHelper::getThumbnailPath($image->file_path, '300x300');
            $filePath = str_replace(urlencode("#"),"#",$filePath);
            $output[$index][] = ['type'=>'image','value'=>'./'.$filePath];
            $output[$index][] = $item->sku;
            $output[$index][] = $products[$item->product_id]->template_no;
            $output[$index][] = $item->qty_ordered;
            $output[$index][] = Options::ringTypes($item->size_type);
            $last_item_info = json_decode($item->last_item_info);
            $output[$index][] = '尺码:'.(isset($last_item_info->size_original)?$last_item_info->size_original:$item->size_original).' [美国码:'.(isset($last_item_info->size_us)?$last_item_info->size_us:$item->size_us).']';
            $output[$index][] = '尺码:'.$item->size_original.' [美国码:'.$item->size_us.']';

            if(isset($last_item_info->engravings)){
                $output[$index] = '刻字: '.html_entity_decode($last_item_info->engravings);
            }else{
                $output[$index][] = '';
            }

            $engravings = html_entity_decode($item->engravings);
            if(!empty($engravings)){
                $output[$index][] = '刻字: '.$engravings;
            }else{
                $output[$index][] = '';
            }
            $output[$index][] = ItemStatus::allStatus($item->item_status);
            $output[$index][] = date("Y-m-d H:i:s",$item->order->process_at);
            $output[$index][] = date("Y-m-d H:i:s",$item->order->process_at+$item->getDeliveryTime());
        }
        return $output;
    }
}