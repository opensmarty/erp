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
use app\helpers\ItemStatus;
use app\models\order\Item;
use app\models\order\Order;
use app\models\product\Product;
use renk\yiipal\components\ExportData;
use renk\yiipal\helpers\FileHelper;
use Yii;
class TaobaoController extends BaseController{

    /**
     * 订单列表
     * @return string
     */
    public function actionTaobao(){
        $status = ItemStatus::purchaseStatus();
        $status = array_keys($status);
        $searchModel = new Item();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams,null,[
            ['in','item_status',$status],
            ['order.order_type'=>Order::ORDER_TYPE_TB],
//            ['order.approved'=>1],
            ['order.blocked'=>'0'],
            ['<>','order.status','pending'],
            ['in','order.paused',[0,2]],
            ['<>','order_item.item_status','shipped']
        ]);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * 开始采购
     * @return array
     */
    public function actionStartPurchase(){
        $posts = Yii::$app->request->post();
        if(!empty($posts['ids'])) {
            $ids = explode(",", $posts['ids']);
            $model = new Item();
            $model->startPurchase($ids);
            return $this->json_output();
        }else{
            return $this->json_output(['status'=>0,'msg'=>'请选择要采购的订单']);
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
            $model = new Item();
            $model->completePurchase($ids);
            return $this->json_output();
        }else{
            return $this->json_output(['status'=>0,'msg'=>'请选择要采购的订单']);
        }
    }

    public function actionExport(){
        $posts = Yii::$app->request->post();
        if(!empty($posts['ids'])) {
            $ids = explode(",", $posts['ids']);
            return $this->exportTaobaoOrder($ids);
            if(empty($validIds)){
                return $this->json_output(['status'=>0,'msg'=>'请选择【待配货】的订单']);
            }else{
                return $this->exportForStartPicking($validIds);
            }

        }else{
            return $this->json_output(['status'=>0,'msg'=>'请选择要配货的订单']);
        }
    }

    /**
     * 导出
     * @param $ids
     * @return array
     */
    private function exportTaobaoOrder($ids){
        $items = Item::find()->with('product')->with('order')
            ->innerJoin('order','order_item.order_id = order.id')->where(['in','order_item.id',$ids])->orderBy('order_id DESC')->all();
        $header = $this->createExportHeader();
        $productIds = [];
        foreach($items as $model){
            $productIds[] = $model->product_id;
        }
        $data = $this->formatExportData($items);
        $objExportData = new ExportData($header,$data);
        $objExportData->createExcel($header,$data);
        $path = 'download/distribution/'.date("Y-m-d").'/'.'products-taobao-'.date('Y-m-d-H-i-s').'.xls';
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
            '订单号',
            '物流公司',
            'SKU',
            '图片',
            '网站尺码',
            '刻字',
            '配货数量',
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
        $output = [];
        $productType = ['none'=>'','men'=>'(男)','women'=>'(女)'];
        foreach($items as $index=>$item){
            $output[$index][] = $item->order->ext_order_id;
            $output[$index][] = $item->order->increment_id;
            $output[$index][] = $item->order->shipping_method;
            $output[$index][] = $item->sku;
            $files = $item->product->getFiles();
            $filePath = FileHelper::getThumbnailPath($files[0]->file_path, '300x300');
            $filePath = str_replace(urlencode("#"),"#",$filePath);
            $output[$index][] = ['type'=>'image','value'=>'./'.$filePath];
            $output[$index][] = $item->size_original.$productType[$item->size_type];
            $output[$index][] = '刻字: '.html_entity_decode($item->engravings).$productType[$item->engravings_type];
            $output[$index][] = $item->qty_ordered;
            $output[$index][] = ItemStatus::allStatus($item->item_status);
        }
        return $output;
    }
}