<?php
/**
 * CountCoupon.php 
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

class CountCoupon extends BaseModel{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'order';
    }
    
    public function getCountCoupon($params){
        $query = Order::find();
        
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
        $data = [];
        $output = [];
        foreach($orders as $order){
            $date = date('Y-m-d',$order->created_at);
            $coupon = strtolower($order->coupon_code);
            $from = ($order->from == 'mobile') ? 'mobile' : 'pc';
            
            //总订单数
            if(!isset($data[$date]['total'][$from])){
                $data[$date]['total'][$from] = 1;
            } else {
                $data[$date]['total'][$from]++;
            }
            
            if(!empty($coupon) && $coupon != 'null'){
                //coupon总使用量
                if(!isset($data[$date]['used'][$from])){
                    $data[$date]['used'][$from] = 1;
                } else {
                    $data[$date]['used'][$from]++;
                }
                
                //每种coupon的使用量
                if(!isset($data[$date]['coupon_code'][$coupon][$from])){
                    $data[$date]['coupon_code'][$coupon][$from] = 1;
                } else {
                    $data[$date]['coupon_code'][$coupon][$from]++;
                }
            }
        }
        
        foreach($data as $k=>$v){
            //M端coupon使用量
            $coupon_used_mobile = isset($v['used']['mobile']) ? $v['used']['mobile'] : 0;
            
            //PC端coupon使用量
            $coupon_used_pc = isset($v['used']['pc']) ? $v['used']['pc'] : 0;
            
            //coupon使用总量
            $coupon_used = $coupon_used_mobile + $coupon_used_pc;
            
            
            //M端总单量
            $total_mobile = isset($v['total']['mobile']) ? $v['total']['mobile'] : 0;
            
            //PC端总单量
            $total_pc = isset($v['total']['pc']) ? $v['total']['pc'] : 0;
            
            //总单量
            $total = $total_mobile + $total_pc;
            
            if(isset($v['coupon_code']) && is_array($v['coupon_code'])){
                foreach($v['coupon_code'] as $_coupon => $_number){
                    if($_coupon != 'null'){
                        //coupon_code
                        $output[$k][$_coupon]['coupon_code'] = $_coupon;
                        
                        //使用量
                            //M使用量
                            $output[$k][$_coupon]['used_mobile'] = isset($_number['mobile']) ? $_number['mobile'] : 0;
                            
                            //PC使用量
                            $output[$k][$_coupon]['used_pc'] = isset($_number['pc']) ? $_number['pc'] : 0;
                            
                            //总使用量
                            $output[$k][$_coupon]['used'] = $output[$k][$_coupon]['used_mobile'] + $output[$k][$_coupon]['used_pc'];
                        
                        //总单数
                            //M订单数
                            $output[$k][$_coupon]['total_mobile'] = $total_mobile;
                            
                            //PC订单数
                            $output[$k][$_coupon]['total_pc'] = $total_pc;
                            
                            //总订单数
                            $output[$k][$_coupon]['total'] = $total;
                        
                        //coupon使用量占比
                        if($coupon_used != 0){
                            $output[$k][$_coupon]['used_rate'] = round($output[$k][$_coupon]['used']/$coupon_used,4);
                            $output[$k][$_coupon]['used_rate_percent'] = CommonHelper::number2Percent($output[$k][$_coupon]['used']/$coupon_used);
                        } else {
                            $output[$k][$_coupon]['used_rate'] = 0;
                            $output[$k][$_coupon]['used_rate_percent'] = 0;
                        }
                        
                        //coupon量与总订单占比
                        if($total != 0){
                            $output[$k][$_coupon]['total_rate_percent'] = CommonHelper::number2Percent($output[$k][$_coupon]['used']/$total);
                        } else {
                            $output[$k][$_coupon]['total_rate_percent'] = 0;
                        }
                    }
                }
            }
            
            //排序
            if(isset($output[$k]) && !empty($output[$k])){
                $used_rate = [];
                foreach ( $output[$k] as $key => $row ){
                    $used_rate[$key] = $row ['used_rate'];
                }
                array_multisort($used_rate, SORT_DESC, $output[$k]);
                
                $_coupon = 'coupon总计';
                $output[$k][$_coupon]['coupon_code'] = $_coupon;
                $output[$k][$_coupon]['used'] = $coupon_used;
                $output[$k][$_coupon]['used_mobile'] = $coupon_used_mobile;
                $output[$k][$_coupon]['used_pc'] = $coupon_used_pc;
                $output[$k][$_coupon]['used_rate_percent'] = '';
                $output[$k][$_coupon]['total_rate_percent'] = '';
                $output[$k][$_coupon]['total'] = '';
                $output[$k][$_coupon]['total_mobile'] = '';
                $output[$k][$_coupon]['total_pc'] = '';
                
                $_coupon = 'coupon总计与总订单、M端、PC端占比';
                $output[$k][$_coupon]['coupon_code'] = $_coupon;
                $output[$k][$_coupon]['used'] = ($total == 0) ? 0 : CommonHelper::number2Percent($coupon_used/$total);
                $output[$k][$_coupon]['used_mobile'] = ($total_mobile == 0) ? 0 : CommonHelper::number2Percent($coupon_used_mobile/$total_mobile);
                $output[$k][$_coupon]['used_pc'] = ($total_pc == 0) ? 0 : CommonHelper::number2Percent($coupon_used_pc/$total_pc);
                $output[$k][$_coupon]['used_rate_percent'] = '';
                $output[$k][$_coupon]['total_rate_percent'] = '';
                $output[$k][$_coupon]['total'] = '';
                $output[$k][$_coupon]['total_mobile'] = '';
                $output[$k][$_coupon]['total_pc'] = '';
            }
        }
        return $output;
    }
}