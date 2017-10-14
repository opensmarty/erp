<?php
/**
 * OrderController.php 
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/8/31
 */

namespace app\controllers\sales;


use app\controllers\BaseController;
use app\helpers\CommonHelper;
use app\models\sales\SalesOrder;
use yii;

class OrderController extends BaseController{

    public $colorList = ['#c23531','#2f4554', '#61a0a8', '#d48265', '#91c7ae','#749f83',  '#ca8622', '#bda29a','#6e7074', '#546570', '#c4ccd3'];

    /**
     * 订单分析
     */
    public function actionIndex(){
        if($this->isPost()){
            $created_at = $this->post('created_at',[]);
            $view_type = $this->post('view_type','day');
            $analyse_type = $this->post('analyse_type','total');
            $dateRanges = $this->getDateRanges($created_at,$view_type);
            $data = $this->getOrderList($dateRanges);
            $outputData = [];
            if($analyse_type == 'total'){
                $item = [];
                $item['name'] = '订单总量';
                $item['type'] = 'bar';
                $item['data'] = [];
                $label = [];
                foreach($data as $index => $row){
                    $label[] = "条件".($index+1);
                    $dataItem = [];
                    $dataItem['value'] = $row;
                    $dataItem['itemStyle']['normal']['color'] = $this->colorList[$index];
                    $item['data'][] = $dataItem;
                }
                $item['data'] = array_reverse($item['data']);
                $outputData[] = $item;
                return $this->json_output(['data'=>['label'=>array_reverse($label),'legend'=>$label,'data'=>$outputData]]);
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

    private function getOrderList($dateRanges){
        $website = $this->post('website',[]);
        $country = $this->post('country',[]);
        $client = $this->post('client',[]);
        $payment_status = $this->post('payment_status',[]);
        $order_type = $this->post('order_type',[]);
        $shipping_method = $this->post('shipping_method',[]);
        $refund_exchange = $this->post('refund_exchange',[]);
        $order_status = $this->post('order_status',[]);
        $coupon_code = $this->post('coupon_code',[]);
        $created_at = $this->post('created_at',[]);
        $view_type = $this->post('view_type','day');
        $analyse_type = $this->post('analyse_type','total');
        $conditions = [];
        foreach($website as $index => $value){
            $item = [];
            $item[] = ['field'=>'source','value'=>$website[$index]];

            $item[] = ['field'=>'country_id','value'=>$country[$index]];

            $item[] = ['field'=>'from','value'=>$client[$index]];

            $item[] = ['field'=>'payment_status','value'=>$payment_status[$index]];

            $item[] = ['field'=>'order_type','value'=>$order_type[$index]];

            $item[] = ['field'=>'shipping_method','value'=>$shipping_method[$index]];

            $item[] = ['field'=>'refund_exchange','value'=>$refund_exchange[$index]];

            $item[] = ['field'=>'status','value'=>$order_status[$index]];
            if(isset($coupon_code[$index])){
                $item[] = ['field'=>'coupon_code','value'=>$coupon_code[$index]];
            }else{
                $item[] = ['field'=>'coupon_code','value'=>''];
            }

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
        $results = $salesOrder->getOrderList($conditions,$view_type,$analyse_type);
        if($analyse_type == 'total'){
            return $results;
        }
        foreach($results as &$row){
            $row = array_values(array_merge($dateRanges,$row));
        }
        return $results;
    }

    /**
     * 获取日期区间
     * @param $dateList
     * @param string $viewType
     * @return array
     */
    private function getDateRanges($dateList,$viewType='day'){
        $startDate = date("y-m-01");
        $endDate = date("y-m-d");
        if(!empty($dateList)){
            if(isset($dateList[0]) && !empty($dateList[0])){
                $date = explode("/", $dateList[0]);
                $startDate = $date[0];
                $endDate = $date[1];
            }
        }

        $interval = 'P1D';
        $format = 'm月d日';
        switch($viewType){
            case 'week':
                $interval = 'P1W';
                $format = 'W周';
                break;
            case 'month':
                $interval = 'P1M';
                $format = 'y年m月';
                break;
            case 'year':
                $interval = 'P1Y';
                $format = 'Y年';
                break;
        }

        $dateRanges = CommonHelper::getDatePeriod($startDate,$endDate,$interval,$format);
        $dateRanges = array_fill_keys($dateRanges,0);
        return $dateRanges;
    }
}