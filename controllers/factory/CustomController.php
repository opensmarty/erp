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
use app\models\order\Item;
use app\models\product\Product as ProductModel;
use app\models\product\Product;
use renk\yiipal\components\ExportData;
use renk\yiipal\helpers\ArrayHelper;
use renk\yiipal\helpers\FileHelper;
use Yii;
class CustomController extends BaseController{

    /**
     * 定制生产订单列表
     * @return string
     */
    public function actionCustom(){
        $searchModel = new Item();
        $status = ItemStatus::customStatusOptionsForFactory();
        $status = array_keys($status);
        $orderStatus = ['processing','waiting_production','in_production','waiting_accept'];
        //产品待定的时候，不能生产
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams,null,[
            ['in','item_status',$status],
            ['in','order.paused',[0,2]],
            ['in','order.status',$orderStatus],
//            ['order.approved'=>1],
            ['<>','order.status','pending'],
        ]);
        return $this->render('index', [
            'searchModel' => $searchModel,
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
            $model = new Item();
            $model->startProduce($ids);
            return $this->json_output();
        }else{
            return $this->json_output(['status'=>0,'msg'=>'请选择要生产的订单']);
        }
    }

    /**
     * 确认加急（工厂）
     * @return array
     */
    public function actionExpeditedConfirm(){
        $posts = Yii::$app->request->post();
        if(!empty($posts['ids'])){
            $ids = explode(",",$posts['ids']);
            $model = new Item();
            $model->expeditedConfirm($ids);
            return $this->json_output();
        }else{
            return $this->json_output(['status'=>0,'msg'=>'请选择要确认加急的订单']);
        }
    }

    /**
     * 请求验收
     */
    public function actionRequestAccept($id){
        if($this->isPost()){
            $posts = Yii::$app->request->post();
            if($posts['number']>0){
                $model = new Item();
                $model->requestAccept($id, $posts['number']);
                return $this->json_output();
            }else{
                return $this->json_output(['status'=>0,'msg'=>'数量必须大于0.']);
            }
        }else{
            $orderItem = Item::find()->where(['id'=>$id])->one();
            return $this->renderAjax('//fragment/request-accept',['modal'=>$orderItem]);
        }

    }


    /**
     * 导出定制款产品
     */
    public function actionExportForCustomProduce(){
        $posts = Yii::$app->request->post();
        if(!empty($posts['ids'])) {
            $ids = explode(",", $posts['ids']);
            return $this->exportForCustomProduce($ids);
        }else{
            return $this->json_output(['status'=>0,'msg'=>'请选择要导出的订单']);
        }
    }


    /**
     * 导出产品数据
     * @param $ids
     * @return array
     */
    private function exportForCustomProduce($ids){
        $items = Item::find()->with('order')
            ->innerJoin('order','order_item.order_id = order.id')->where(['in','order_item.id',$ids])->all();
        $header = $this->createExportHeader();
        $data = $this->formatExportData($items);
        $objExportData = new ExportData($header,$data);
        $objExportData->createExcel($header,$data);
        $path = 'download/factory/custom/'.date("Y-m-d").'/'.'products-'.date('Y-m-d-H-i-s').'.xls';
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
            $output[$index][] = '尺码:'.$item->size_original.' [美国码:'.$item->size_us.']';
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