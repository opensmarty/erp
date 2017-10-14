<?php
/**
 * ItemStatus.php 
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/5/20
 */

namespace app\helpers;


use app\models\order\Item;
use app\models\order\Order;

class ItemStatus {

    /**
     * 支付状态选项
     * @return array
     */
    public static function paymentStatusOptions($status = false){
        $options = [
            'canceled'					=> 'Canceled',
            'closed'					=> 'Closed',
            'complete'					=> 'Complete',
            'fraud'						=> 'Suspected Fraud',
            'holded'					=> 'On Hold',
            'payment_review'			=> 'Payment Review',
            'paypal_canceled_reversal'	=> 'PayPal Canceled Reversal',
            'paypal_reversed'			=> 'PayPal Reversed',
            'pending'					=> 'Pending',
            'pending_payment'			=> 'Pending Payment',
            'pending_paypal'			=> 'Pending PayPal',
            'processing'				=> 'Processing',
        ];

        if($status === false){
            return $options;
        }else{
            return isset($options[$status])?$options[$status]:'';
        }
    }

    /**
     * 定制款配货
     * @return array
     */
    public static function customStatusOptionsForDistribution()
    {
        $options = [
            Item::TASK_STATUS_WAITING_PRODUCTION		    => '等待生产',
            Item::TASK_STATUS_IN_PRODUCTION			        => '生产中',
            Item::TASK_STATUS_REWORK					    => '返修中',
            Item::TASK_STATUS_WAIT_ACCEPT			        => '等待验收',
            Item::TASK_STATUS_WAITING_REPAIR			    => '等待返修',
            Item::TASK_STATUS_BEING_REPAIRED			    => '开始返修',
            Item::TASK_STATUS_PRODUCT_PASSED			    => '验货通过',
            Item::TASK_STATUS_WAITING_SHIPPED		        => '待发货',
        ];

        return $options;
    }

    /**
     * 定制款工厂
     * @return array
     */
    public static function customStatusOptionsForFactory($status = false)
    {
        $options = [
            Item::TASK_STATUS_WAITING_PRODUCTION		    => '等待生产',
            Item::TASK_STATUS_IN_PRODUCTION			        => '生产中',
            Item::TASK_STATUS_REWORK					    => '返修中',
            Item::TASK_STATUS_WAIT_ACCEPT			        => '等待验收',
            Item::TASK_STATUS_WAITING_REPAIR			    => '等待返修',
            Item::TASK_STATUS_BEING_REPAIRED			    => '开始返修',
        ];

        if($status === false){
            return $options;
        }else{
            return isset($options[$status])?$options[$status]:'';
        }
    }


    /**
     * 全局产品状态
     * @return array
     */
    public static function globalStatus()
    {
        $options = [
            Item::TASK_STATUS_PENDING				=> '待处理',
            Item::TASK_STATUS_WAITING_SHIPPED		=> '待发货',
            Item::TASK_STATUS_SHIPPED				=> '已发货',
            Item::TASK_STATUS_PROCESSING			=> '备货中',
            Item::TASK_STATUS_CANCELLED				=> '已取消',
            Item::TASK_STATUS_COMPLETE				=> '交易完成',
        ];

        return $options;
    }

    /**
     * 外购存款状态
     * @param bool $status
     * @return array|string
     */
    public static function purchaseStatus()
    {
        $options = [
            Item::TASK_STATUS_PENDING_PURCHASE	    => '待采购',
            Item::TASK_STATUS_PURCHASE			    => '采购中',
            Item::TASK_STATUS_PURCHASE_COMPLETED	=> '采购完成',
            Item::TASK_STATUS_WAITING_SHIPPED	    => '待发货',
        ];
        return $options;
    }


    /**
     * 配货状态选项
     * @param bool $status
     * @return array
     */
    public static function statusOptionForDistribution($status = false)
    {
        $options = [
            Item::TASK_STATUS_PICK_WAITING			=> '待配货',
            Item::TASK_STATUS_PICKING				=> '配货中',
            Item::TASK_STATUS_WAITING_SHIPPED		=> '待发货',
        ];
        return $options;
    }


    /**
     * 售后状态选项
     * @return array
     */
    public static function afterSalesStatusOptions(){
        $options = [
            Item::TASK_STATUS_RETURN_PROCESS        => '退货中（整单）',
            Item::TASK_STATUS_RETURN_PROCESS_PART   => '退货中（部分）',
            Item::TASK_STATUS_RETURN_COMPLETED      => '退货完成（整单）',
            Item::TASK_STATUS_RETURN_COMPLETED_PART => '退货完成（部分）',
            Item::TASK_STATUS_EXCHANGE_PROCESS      => '换货中（整单）',
            Item::TASK_STATUS_EXCHANGE_PROCESS_PART => '换货中（部分）',
            Item::TASK_STATUS_EXCHANGE_COMPLETED    => '换货完成（整单）',
            Item::TASK_STATUS_EXCHANGE_COMPLETED_PART => '换货完成（部分）',
        ];
        return $options;
    }

    /**
     * 所有状态
     * @param null $status
     * @return array
     */
    public static function allStatus($status=false,$prepend=false)
    {
        $globalStatus = self::globalStatus();
        $statusOptionForDistribution = self::statusOptionForDistribution();
        $customStatusOptionsForDistribution = self::customStatusOptionsForDistribution();
        $purchaseStatus = self::purchaseStatus();
        $afterSalesStatusOptions = self::afterSalesStatusOptions();
        if($status !== false){
            $all = array_merge($globalStatus,$statusOptionForDistribution, $customStatusOptionsForDistribution,$purchaseStatus,$afterSalesStatusOptions);
            return isset($all[$status])?$all[$status]:'';
        }

        $options = array(
            '全局状态'	=> $globalStatus,
            '库存款状态'	=> $statusOptionForDistribution,
            '定制款状态'	=> $customStatusOptionsForDistribution,
            '淘宝款状态'	=> $purchaseStatus,
            '售后状态'	=> $afterSalesStatusOptions,

        );

        if($prepend){
            array_unshift($options,$prepend);
        }

        return $options;
    }


    /**
     * 订单跟踪状态
     * @param null $status
     * @return array
     */
    public static function trackStatus($status=false){
        $options = [
            Order::TASK_STATUS_NORMAL                   =>'正常',
            Order::TASK_STATUS_ADDRESS_CHANGED          =>'地址变更',
            Order::TASK_STATUS_ITEM_CHANGED             =>'产品变更',
            Order::TASK_STATUS_SHIPPING_METHOD_CHANGED  =>'物流方式变更',
            Order::TASK_STATUS_CHANGE_CONFIRMED         =>'变更确认',
        ];

        if($status === false){
            return $options;
        }else{
            return isset($options[$status])?$options[$status]:'';
        }
    }

    /**
     * 工厂变更确认
     * @param bool $status
     * @return array|string
     */
    public static function factoryConfirmStatus($status = false){
        $options = [
            'pending'                   =>'等待确认',
            'solved'                    =>'已经确认',
        ];

        if($status === false){
            return $options;
        }else{
            return isset($options[$status])?$options[$status]:'';
        }
    }

    /**
     * 订单类型
     */
    public static function orderTypeOptions($status=false){
        $options = [
            Order::ORDER_TYPE_CUSTOM    => '定制单',
            Order::ORDER_TYPE_STOCK     => '库存单',
            Order::ORDER_TYPE_TB        => '淘宝单',
            Order::ORDER_TYPE_MIXTURE   => '混合单',
        ];

        if($status === false){
            return $options;
        }else{
            return isset($options[$status])?$options[$status]:'';
        }
    }
}