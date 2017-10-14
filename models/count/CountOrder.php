<?php
/**
 * CountOrder.php 
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/8/31
 */

namespace app\models\count;

use app\models\BaseModel;
use app\models\order\Order;
use app\helpers\CommonHelper;

class CountOrder extends BaseModel{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'order';
    }
    
    public function getCountOrder($params){
        $query = Order::find()
        ->orderBy("created_at ASC");
        
        if(isset($params['Order']['created_at']) && !empty($params['Order']['created_at'])){
            $dateRange = explode("/", $params['Order']['created_at']);
            $start = strtotime($dateRange[0] ." 00:00:00");
            $end = strtotime($dateRange[1] ." 23:59:59");
        }else{
            $start  = strtotime(date('Y-m-d',time()) ." 00:00:00");
            $end  = strtotime(date('Y-m-d',time())  ." 23:59:59");
        }
        
        $query->andWhere(['>=','order.created_at',$start])
        ->andWhere(['<=','order.created_at',$end]);
        
        if(isset($params['source']) && !empty($params['source'])){
            $query->andWhere(['order.source'=>$params['source']]);
        }
        
        $results = $query->all();
        return $this->formateData($results);
    }
    
    private function formateData($orders){
        /*
        array(
            'order_total' => 0,//总订单
            
            'payment_method' => array(
                'paypal' =>array(
                    'pending' => 0,
                    'processing' => 0,
                ),
                'affirm' =>array(
                    'pending' => 0,
                    'processing' => 0,
                ),
            )
            
            'payment_status' => array(
                'pending' => 0,
                'processing' => 0,
            )
        )
        */
        
        $data = [];
        $output = [];
        foreach($orders as $order){
            $date = date('Y-m-d',$order->created_at);
            $from = ($order->from == 'mobile') ? 'mobile' : 'pc';
            
            //总订单
            if(!isset($data[$date][$from]['order_total'])){
                $data[$date][$from]['order_total'] = 1;
            } else {
                $data[$date][$from]['order_total']++;
            }
            
            //订单支付方式
            if(!isset($data[$date][$from]['payment_method'][$order->payment_method][$order->payment_status])){
                $data[$date][$from]['payment_method'][$order->payment_method][$order->payment_status] = 1;
            } else {
                $data[$date][$from]['payment_method'][$order->payment_method][$order->payment_status]++;
            }
            
            //订单支付状态
            if(!isset($data[$date][$from]['payment_status'][$order->payment_status])){
                $data[$date][$from]['payment_status'][$order->payment_status] = 1;
            } else {
                $data[$date][$from]['payment_status'][$order->payment_status]++;
            }
        }
        
        foreach($data as $k=>$v){
            //移动端
                //总订单
                $output[$k]['mobile_order_total'] = isset($v['mobile']['order_total']) ? $v['mobile']['order_total'] : 0;
                
                //已支付
                $output[$k]['mobile_payment_status_processing'] = isset($v['mobile']['payment_status']['processing']) ? $v['mobile']['payment_status']['processing'] : 0;
                
                //Paypal支付
                $output[$k]['mobile_payment_method_paypal_express_processing'] = isset($v['mobile']['payment_method']['paypal_express']['processing']) ? $v['mobile']['payment_method']['paypal_express']['processing'] : 0;
            
            //PC端
                //总订单
                $output[$k]['pc_order_total'] = isset($v['pc']['order_total']) ? $v['pc']['order_total'] : 0;
                
                //已支付
                $output[$k]['pc_payment_status_processing'] = isset($v['pc']['payment_status']['processing']) ? $v['pc']['payment_status']['processing'] : 0;
                
                //Affirm支付
                $output[$k]['pc_payment_method_affirm_processing'] = isset($v['pc']['payment_method']['affirm']['processing']) ? $v['pc']['payment_method']['affirm']['processing'] : 0;
                
                //选择Affirm支付的订单
                $pc_payment_method_affirm = 0;
                if(isset($v['pc']['payment_method']['affirm'])){
                    foreach($v['pc']['payment_method']['affirm'] as $number){
                        $pc_payment_method_affirm += $number;
                    }
                }
                
                //Affirm支付成功率 = Affirm支付成功 / 选择Affirm支付
                if($pc_payment_method_affirm != 0){
                    $output[$k]['pc_affirm_rate'] = CommonHelper::number2Percent($output[$k]['pc_payment_method_affirm_processing']/$pc_payment_method_affirm);
                } else {
                    $output[$k]['pc_affirm_rate'] = 0;
                }
                
                //Paypal支付
                $output[$k]['pc_payment_method_paypal_express_processing'] = isset($v['pc']['payment_method']['paypal_express']['processing']) ? $v['pc']['payment_method']['paypal_express']['processing'] : 0;
                
                //选择Affirm支付的订单
                $pc_payment_method_paypal_express = 0;
                if(isset($v['pc']['payment_method']['paypal_express'])){
                    foreach($v['pc']['payment_method']['paypal_express'] as $number){
                        $pc_payment_method_paypal_express += $number;
                    }
                }
                
                //Paypal支付成功率 = Paypal支付成功 / 选择Paypal支付
                if($pc_payment_method_paypal_express != 0){
                    $output[$k]['pc_paypal_express_rate'] = CommonHelper::number2Percent($output[$k]['pc_payment_method_paypal_express_processing']/$pc_payment_method_paypal_express);
                } else {
                    $output[$k]['pc_paypal_express_rate'] = 0;
                }
                
                //Pending订单
                $output[$k]['pc_payment_status_pending'] = isset($v['pc']['payment_status']['pending']) ? $v['pc']['payment_status']['pending'] : 0;
                
                //Affirm Pending
                $output[$k]['pc_payment_method_affirm_pending'] = isset($v['pc']['payment_method']['affirm']['pending']) ? $v['pc']['payment_method']['affirm']['pending'] : 0;
                
                //Paypal Pending
                //$output[$k]['pc_payment_method_paypal_express_pending'] = isset($v['pc']['payment_method']['paypal_express']['pending']) ? $v['pc']['payment_method']['paypal_express']['pending'] : 0;
                //Paypal Pending = Pending订单 - Affirm Pending
                $output[$k]['pc_payment_method_paypal_express_pending'] = $output[$k]['pc_payment_status_pending'] - $output[$k]['pc_payment_method_affirm_pending'];
                
                //其他订单：除了processing和pending以外的订单
                $output[$k]['pc_order_other'] = $output[$k]['pc_order_total'] - $output[$k]['pc_payment_status_processing'] - $output[$k]['pc_payment_status_pending'];
                
                //PC支付率
                if($output[$k]['pc_order_total'] != 0){
                    $output[$k]['pc_processing_rate'] = CommonHelper::number2Percent($output[$k]['pc_payment_status_processing']/$output[$k]['pc_order_total']);
                } else {
                    $output[$k]['pc_processing_rate'] = 0;
                }
            
            //整站
                //总订单
                $output[$k]['order_total'] = $output[$k]['mobile_order_total'] + $output[$k]['pc_order_total'];
                
                //已支付
                $output[$k]['order_total_processing'] = $output[$k]['pc_payment_status_processing'] + $output[$k]['mobile_payment_status_processing'];
                
                //支付率
                if($output[$k]['order_total'] != 0){
                    $output[$k]['processing_rate'] = CommonHelper::number2Percent($output[$k]['order_total_processing']/$output[$k]['order_total']);
                } else {
                    $output[$k]['processing_rate'] = 0;
                }
        }
        return $output;
    }
}