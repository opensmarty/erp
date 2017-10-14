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
use renk\yiipal\components\ExportData;
use renk\yiipal\helpers\FileHelper;
use Yii;
class CustomController extends BaseController{

    /**
     * 订单列表
     * @return string
     */
    public function actionCustom(){

        $customConditions = array_keys(ItemStatus::customStatusOptionsForDistribution());
        $searchModel = new Item();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams,null,[
            ['order.order_type'=>Order::ORDER_TYPE_CUSTOM],
            ['in','order.status',$customConditions],
            "order.paused = 0 OR order.paused = 2 OR (order.paused=1 AND order.status<>'waiting_shipped')",
            ['order.approved'=>1],
            "order.blocked =0 OR (order.blocked=1 AND order.status<>'waiting_shipped')",
            ['<>','order.status','pending'],
            ['<>','order_item.item_status','shipped']
        ]);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * 验收通过
     * @param $id
     */
    public function actionAcceptRequest($id){
        if($this->isPost()){
            $posts = Yii::$app->request->post();
            if($posts['number']>=0){
                $model = new Item();
                $model->acceptRequest($id, $posts);
                return $this->json_output();
            }else{
                return $this->json_output(['status'=>0,'msg'=>'次品数量必须大于等于0.']);
            }
        }else{
            $orderItem = Item::find()->where(['id'=>$id])->one();
            return $this->renderAjax('//fragment/accept-request',['modal'=>$orderItem]);
        }

    }

    /**
     * 发货
     * @return array
     */
    public function actionShip($id=false,$type='item'){
        $posts = Yii::$app->request->post();
        if(empty($id)){
            $ids = $posts['ids'];
            $type = $this->post('type','item');
        }else{
            $ids = $id;
        }
        if(!empty($ids)) {
            $ids = explode(",", $ids);
            $model = new Item();
            $invalidIds = $model->ship($ids,$type);
            if(empty($invalidIds)){
                return $this->json_output();
            }else{
                return $this->exportInvalidOrdersForShipping($invalidIds,$type);
            }

        }else{
            return $this->json_output(['status'=>0,'msg'=>'请选择要发货的订单']);
        }
    }


    /**
     * 导出发货订单
     */
    private function exportInvalidOrdersForShipping($ids,$type='item'){
        if($type == 'item'){
            $filter = 'order_item.id';
        }else{
            $filter = 'order_item.order_id';
        }

        $items = Item::find()->with('order')->with('product')
            ->innerJoin('order','order_item.order_id = order.id')->where(['in',$filter,$ids])->all();
        $header = $this->createExportHeader();
        $data = $this->formatExportData($items);
        $objExportData = new ExportData($header,$data);
        $objExportData->createExcel($header,$data);
        $path = 'download/distribution/custom/'.date("Y-m-d").'/'.'orders-'.date('Y-m-d-H-i-s').'.xls';
        $objExportData->saveFileTo($path);
        return $this->json_output(['data'=>['/'.$path],'msg'=>'部分单子不能发货，请参见导出的Excel']);
    }


    /**
     * 配货单头部
     * @return array
     */
    private function createExportHeader(){
        $header = [
            '编号',
            '订单号',
            'SKU',
            '图片',
            '网站尺码',
            '刻字',
            '发货数量',
            '产品状态',
            '订单状态',
            '订单待定',
            '跟踪状态',
            '验收状态',
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
            $output[$index][] = $item->order->ext_order_id;
            $output[$index][] = $item->order->increment_id;
            $output[$index][] = $item->sku;
            $files = $item->product->getFiles();
            $filePath = FileHelper::getThumbnailPath($files[0]->file_path, '300x300');
            $filePath = str_replace(urlencode("#"),"#",$filePath);
            $output[$index][] = ['type'=>'image','value'=>'./'.$filePath];
            $output[$index][] = $item->size_original.$productType[$item->size_type];;
            $engravingsType = ['none'=>'','men'=>'(男)','women'=>'(女)'];
            $output[$index][] = '刻字: '.html_entity_decode($item->engravings).$engravingsType[$item->engravings_type];
            $output[$index][] = $item->qty_ordered;
            $output[$index][] = ItemStatus::allStatus($item->item_status);
            $output[$index][] = ItemStatus::allStatus($item->order->status);
            $paused = [0=>'否',1=>'是',2=>'是'];
            $output[$index][] = $paused[$item->order->paused];
            $output[$index][] = ItemStatus::trackStatus($item->order->last_track_status);
            $output[$index][] = $item->has_rejects ==1?'次品':'正常';
        }
        return $output;
    }
}