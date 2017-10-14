<?php
/**
 * ProductController.php 
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/9/18
 */

namespace app\controllers\sales;


use app\controllers\BaseController;
use app\helpers\CommonHelper;
use app\models\product\Product;
use app\models\sales\SalesOrder;
use app\models\order\Item;
use renk\yiipal\components\ExportData;
use yii\db\Expression;
use renk\yiipal\helpers\FileHelper;
use yii;

class ProductController extends BaseController{

    /**
     * 订单分析
     */
    public function actionIndex(){
        if($this->isPost()){
            $created_at = $this->post('created_at',[]);
            $view_type = $this->post('view_type','day');
            $analyse_type = $this->post('analyse_type','total');
            $dateRanges = CommonHelper::getDateRanges($created_at[0],$view_type);
            $data = $this->getSalesList($dateRanges);
            $outputData = [];
            if($analyse_type == 'total'){
                $item = [];
                $item['name'] = '销售数量';
                $item['type'] = 'bar';
                $item['data'] = $data['total'];
                $outputData[] = $item;
                $item = [];
                $item['name'] = '销售金额';
                $item['type'] = 'bar';
                $item['yAxisIndex'] = 1;
                $item['data'] = $data['turnover'];
                $outputData[] = $item;
                $item = [];
                $item['name'] = '转化率';
                $item['type'] = 'line';
                $item['yAxisIndex'] = 2;
                $item['data'] = array_fill(0,count($data['total']),0);
                $outputData[] = $item;

                $label = [];
                foreach($data['total'] as $index=>$item){
                    $label[] = "条件".($index+1);
                }

                return $this->json_output(['data'=>['label'=>$label,'legend'=>[],'data'=>$outputData]]);
            }
            $label = [];
            foreach($data as $index => $row){
                $item = [];
                $label[] = "条件".($index+1);
                $item['name'] = '条件'.($index+1);
                $item['type'] = 'line';
                $item['data'] = $row;
                $outputData[] = $item;
            }
            return $this->json_output(['data'=>['label'=>array_keys($dateRanges),'legend'=>$label,'data'=>$outputData]]);
        }
        return $this->render('index');
    }

    private function getSalesList($dateRanges){
        $sku = $this->post('sku',[]);
        $website = $this->post('website',[]);
        $magento_cid = $this->post('magento_cid',[]);
        $stone_type = $this->post('stone_type',[]);
        $stone_color = $this->post('stone_color',[]);
        $electroplating_color = $this->post('electroplating_color',[]);
        $cost_price_start = $this->post('cost_price_start',[]);
        $cost_price_end = $this->post('cost_price_end',[]);
        $sales_price_start = $this->post('sales_price_start',[]);
        $sales_price_end = $this->post('sales_price_end',[]);
        $chosen_uid = $this->post('chosen_uid',[]);
        $source = $this->post('source',[]);
        $created_at = $this->post('created_at',[]);
        $view_type = $this->post('view_type','day');
        $analyse_type = $this->post('analyse_type','total');
        $conditions = [];
        foreach($website as $index => $value){
            $item = [];
            $item[] = ['field'=>'website','value'=>$website[$index]];
            $item[] = ['field'=>'magento_cid','value'=>$magento_cid[$index]];
            $item[] = ['field'=>'sku','value'=>trim($sku[$index])];
            $item[] = ['field'=>'stone_type','value'=>$stone_type[$index]];
            $item[] = ['field'=>'stone_color','value'=>$stone_color[$index]];
            $item[] = ['field'=>'electroplating_color','value'=>$electroplating_color[$index]];
            $item[] = ['field'=>'cost_price_start','value'=>$cost_price_start[$index]];
            $item[] = ['field'=>'cost_price_end','value'=>$cost_price_end[$index]];
            $item[] = ['field'=>'sales_price_start','value'=>$sales_price_start[$index]];
            $item[] = ['field'=>'sales_price_end','value'=>$sales_price_end[$index]];
            $item[] = ['field'=>'chosen_uid','value'=>trim($chosen_uid[$index])];
            $item[] = ['field'=>'source','value'=>$source[$index]];

            if($analyse_type == 'total'){
                $item[] = ['field'=>'created_at','value'=>$created_at[$index]];
            }else{
                if(empty($created_at[0])){
                    $item[] = ['field'=>'created_at','value'=>date("y-m-01")."/".date("y-m-d")];
                }else{
                    $item[] = ['field'=>'created_at','value'=>$created_at[0]];
                }

            }

            $conditions[] = $item;
        }

        $salesOrder = new SalesOrder();
        $results = $salesOrder->getSalesList($conditions,$view_type,$analyse_type);
        if($analyse_type == 'total'){
            return $results;
        }
        foreach($results as &$row){
            $row = array_values(array_merge($dateRanges,$row));

        }
        return $results;
    }

    /**
     * Top 100
     * @return string
     */
    public function actionTop100(){

        $searchModel = new SalesOrder();
        $dataProvider = $searchModel->getRank(Yii::$app->request->queryParams);
        return $this->render('top100', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);

    }
    
    /**
     * 导出销量
     * @return array
     */
    public function actionExport(){
        $posts = Yii::$app->request->post();
        if(!empty($posts['ids'])){
            $ids = explode(",",$posts['ids']);
            
            $product_id_ar = Item::find()->select('product_id')->where(['in','id',$ids])->all();
            $product_ids = [];
            foreach ($product_id_ar as $v_product_id_ar){
                $product_ids[] = $v_product_id_ar->product_id;
            }
            
            $items = Item::find()->with('product')
            ->leftJoin('order','order.id=order_item.order_id')
            ->leftJoin('product','product.id=order_item.product_id')
            ->addSelect(new Expression("order_item.id,order_item.product_id,order_item.sku,SUM(order_item.qty_ordered) AS qty_ordered"))
            ->where(["<>","order_item.item_status","cancelled"])
            ->andWhere(['order.payment_status'=>'processing'])
            ->andWhere(['in','product_id', $product_ids])
            ->groupBy("order_item.product_id")
            ->orderBy("qty_ordered DESC")
            ->all();
            
            $header = $this->createExportHeader();
            $data = $this->formatExportData($items);
            $objExportData = new ExportData($header,$data);
            $objExportData->createExcel($header,$data);
            $path = 'download/productSales/'.date("Y-m-d").'/'.date('Y-m-d-H-i-s').'.xls';
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
            'SKU',
            '图片',
            '销量',
            '选款人',
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
        foreach($items as $index=>$item){
            $output[$index][] = $item->sku;
            $files = $item->product->getFiles();
            $filePath = FileHelper::getThumbnailPath($files[0]->file_path, '300x300');
            $filePath = str_replace(urlencode("#"),"#",$filePath);
            $output[$index][] = ['type'=>'image','value'=>'./'.$filePath];
            $output[$index][] = $item->qty_ordered;
            $output[$index][] = $item->product->chosenUser ? $item->product->chosenUser->nick_name : '未知';
        }
        return $output;
    }

    /**
     * 销售历史
     * @param $productId
     * @return array
     */
    public function actionSalesHistory($productId){
        $datePeriod = CommonHelper::getDatePeriod(date("Y-m-d",strtotime("-30 days")),date("Y-m-d"));
        $datePeriod = array_fill_keys(array_values($datePeriod), 0);
        $data = Product::getProductSalesHistory($productId,strtotime("-30 days"),time());
        $product = Product::findOne($productId);
        if(empty($data)){
            $data = [];
        }

        foreach($data as $row){
            $datePeriod[$row['short_date']] = intval($row['qty']);
        }
        return $this->render('sales-history', [
            'data' => $datePeriod,
            'product'=>$product
        ]);
    }
}