<?php
/**
 * Top10.php 
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/8/31
 */

namespace app\models\count;

use app\models\BaseModel;
use app\models\order\Order;

class Pct extends BaseModel{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'order';
    }
    
    public function getPct($params){
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
        
        if(isset($params['payment_status']) && !empty($params['payment_status'])){
            $query->andWhere(['order.payment_status'=>$params['payment_status']]);
        }
        
        $results = $query->all();
        return $this->formateData($results);
    }
    
    private function formateData($orders){
        $data = [];
        $output = [];
        foreach($orders as $order){
            $date = date('Y-m-d',$order->created_at);
            $from = ($order->from == 'mobile') ? 'mobile' : 'pc';
            
            if(!isset($data[$date][$from]['order_count'])){
                $data[$date][$from]['order_count'] = 1;
                $data[$date][$from]['order_grand_total'] = $order->grand_total;
            } else {
                $data[$date][$from]['order_count']++;
                $data[$date][$from]['order_grand_total'] += $order->grand_total;
            }
        }
        
        foreach($data as $k=>$v){
            //移动端
                //订单数
                $output[$k]['mobile_order_count'] = isset($v['mobile']['order_count']) ? $v['mobile']['order_count'] : 0;
                
                //销售总额
                $output[$k]['mobile_order_grand_total'] = isset($v['mobile']['order_grand_total']) ? $v['mobile']['order_grand_total'] : 0;
                
                //客单价 = 销售总额 / 订单数
                if($output[$k]['mobile_order_count'] != 0){
                    $output[$k]['mobile_pct'] = round($output[$k]['mobile_order_grand_total']/$output[$k]['mobile_order_count'],2);
                } else {
                    $output[$k]['mobile_pct'] = 0;
                }
            
            //PC端
                //订单数
                $output[$k]['pc_order_count'] = isset($v['pc']['order_count']) ? $v['pc']['order_count'] : 0;
                
                //销售总额
                $output[$k]['pc_order_grand_total'] = isset($v['pc']['order_grand_total']) ? $v['pc']['order_grand_total'] : 0;
                
                //客单价 = 销售总额 / 订单数
                if($output[$k]['pc_order_count'] != 0){
                    $output[$k]['pc_pct'] = round($output[$k]['pc_order_grand_total']/$output[$k]['pc_order_count'],2);
                } else {
                    $output[$k]['pc_pct'] = 0;
                }

            //整站
                //订单数
                $output[$k]['all_order_count'] = $output[$k]['mobile_order_count'] + $output[$k]['pc_order_count'];
                
                //销售总额
                $output[$k]['all_order_grand_total'] = $output[$k]['mobile_order_grand_total'] + $output[$k]['pc_order_grand_total'];
                
                //客单价 = 销售总额 / 订单数
                if($output[$k]['all_order_grand_total'] != 0){
                    $output[$k]['all_pct'] = round($output[$k]['all_order_grand_total']/$output[$k]['all_order_count'],2);
                } else {
                    $output[$k]['all_pct'] = 0;
                }
        }
        return $output;
    }
}