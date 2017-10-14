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
use app\models\product\ProductDelivered;
use renk\yiipal\components\ExportData;
use renk\yiipal\helpers\ArrayHelper;
use renk\yiipal\helpers\FileHelper;
use Yii;
use app\models\order\Item;

class HistoryController extends BaseController{

    /**
     * 工厂生产历史记录
     * @return string
     */
    public function actionList(){
        $searchModel = new ProductDelivered();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * 导出生产历史
     * @return array
     */
    public function actionExport(){
        $posts = Yii::$app->request->post();
        if(!empty($posts['ids'])) {
            $ids = explode(",", $posts['ids']);
            return $this->exportProduceHistory($ids);
        }else{
            return $this->json_output(['status'=>0,'msg'=>'请选择要导出的订单']);
        }
    }

    /**
     * 导出生产数据
     * @param $ids
     * @return array
     */
    private function exportProduceHistory($ids){
        $items = ProductDelivered::find()
            ->with('customOrder')
            ->with('stockOrder')
//            ->with('orderItems')
//            ->with('stockOrder')
            ->where(['in','id',$ids])
            ->orderBy("created_at DESC")
            ->all();
        $header = $this->createExportHeader();
        $data = $this->formatExportData($items);
        $objExportData = new ExportData($header,$data);
        $objExportData->createExcel($header,$data);
        $path = 'download/factory/history/'.date("Y-m-d").'/'.'products-'.date('Y-m-d-H-i-s').'.xls';
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
            '加急',
            '图片',
            'SKU',
            '生产总数',
            '验货通过数'
        ];
        if(Yii::$app->user->can('/permission/price')){
            $header [] ='单价';
            $header [] ='总价';
        }
        $extra =[
            '尺码',
            '刻字',
            '是否刻字',
            '订单类型',
            '开始时间',
            '交货时间',
            '生产时间',
        ];
        return array_merge($header,$extra);
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
            $order = $item->order_type == 'custom'?$item->customOrder:$item->stockOrder;
            $prefix = $item->order_type == 'custom'?'':"S-";
            $output[$index][] = $prefix.$order->ext_order_id;
            $output[$index][] = (isset($order->expedited) && $order->expedited>0)?"加急":"否";
            $image = $products[$item->product_id]->getMasterImage();
            $filePath = FileHelper::getThumbnailPath($image->file_path, '300x300');
            $filePath = str_replace(urlencode("#"),"#",$filePath);
            $output[$index][] = ['type'=>'image','value'=>'./'.$filePath];
            $output[$index][] = $item->sku;
            $output[$index][] = $item->qty_passed_total.'/'.$item->qty_ordered;
            $output[$index][] = $item->qty_passed;
            if(Yii::$app->user->can('/permission/price')) {
                $output[$index][] = '￥' . $item->price;
                $output[$index][] = '￥' . round($item->price * $item->qty_passed, 2);
            }
            $output[$index][] = ' 美国码:'.$item->size_us;
            $engravings = html_entity_decode($item->engravings);
            if(!empty($engravings)){
                $output[$index][] = '刻字: '.$engravings;
            }else{
                $output[$index][] = '';
            }
            $output[$index][] = empty($engravings)?"否":"是";
            $output[$index][] = ItemStatus::orderTypeOptions($item->order_type);
            $output[$index][] = date("Y-m-d H:i:s",$item->start_at);
            $output[$index][] = date("Y-m-d H:i:s",$item->created_at);

            $time = intval($item->duration_time);
            $day = floor($time / 86400);
            $hour = floor(($time - $day * 86400) / 3600);
            $minute = floor(($time - ($day * 86400) - ($hour * 3600) ) / 60);
            $second = floor($time - ($day * 86400) - ($hour * 3600) - ($minute * 60));
            $duration =  sprintf('%d天%d时%d分%d秒', $day, $hour, $minute, $second);
            $output[$index][] = $duration;
        }
        return $output;
    }
}