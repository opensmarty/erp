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

use app\models\Comment;
use app\models\order\Order;
use app\models\product\ProductTemplate;
use yii\db\Query;

class Options {
    /**
     * 主钻类型
     * @param $stoneType
     * @return array|string
     */
    public static function stoneType($stoneType=false,$prepend=false){
        $options = [
            ''=>'none（无主钻）',
            'cushion'=>'Cushion（长角阶梯型）',
            'emerald'=>'Emerald(祖母绿切割）',
            'heart'=>'Heart(心形)',
            'marquise'=>'Marquise（马眼形）',
            'oval'=>'Oval（椭圆形）',
            'pear'=>'Pear（梨形）',
            'princess'=>'Princess（公主方形）',
            'round'=>'Round（圆形）',
            'radiant'=>'Radiant（明亮切形）',
            'asscher'=>'Asscher（阿斯切）',
            'baguette'=>'Baguette（细长形琢型）',
            'trillion'=>'Trillion（三角形切）',
        ];

        if($prepend){
            array_unshift($options,$prepend);
        }

        if($stoneType === false){
            return $options;
        }else{
            return isset($options[$stoneType])?$options[$stoneType]:'';
        }
    }

    /**
     * 主钻颜色
     * @param $stoneColor
     * @return array|string
     */
    public static function stoneColor($stoneColor=false,$prepend=false){
        $options = [
            ''=>'none（无）',
            'white'=>'White Sapphire（白色）',
            'amaranth-sapphire'=>'Amaranth Sapphire（紫红色）',
            'amethyst'=>'Amethyst（紫色）',
            'aquamarine'=>'Aquamarine（海蓝色）',
            'black'=>'Black（黑色）',
            'emerald'=>'Emerald（祖母绿色）',
            'garnet'=>'Garnet（石榴红色）',
            'lilac-amethyst'=>'Lilac Amethyst（淡紫色）',
            'orange-sapphire'=>'Orange and Sapphire（橘色+宝石蓝）',
            'peridot'=>'Peridot（橄榄绿色）',
            'pink-sapphire'=>'Pink and Sapphire（粉色+宝石蓝）',
            'ruby'=>'Ruby（大红色）',
            'sapphire'=>'Sapphire（宝蓝色）',
            'topaz'=>'Topaz（黄晶色）',
            'white-sapphire'=>'White and Sapphire（白色+宝石蓝）',
            'champagne'=>'Champagne（香槟色）',
            'olive-yellow'=>'Olive Yellow（橄榄黄）',
        ];

        if($prepend){
            array_unshift($options,$prepend);
        }

        if($stoneColor === false){
            return $options;
        }else{
            return isset($options[$stoneColor])?$options[$stoneColor]:'';
        }
    }


    /**
     * 物流方式
     * @param $shippingMethod
     * @return array|string
     */
    public static function shippingMethods($shippingMethod=false,$prepend=false){
        $options = [
            'DHL' =>'DHL',
            'UPS' =>'UPS',
            'EUB'  => 'EUB',
            'ARAMEX'  => 'ARAMEX',
        ];

        if($prepend){
            array_unshift($options,$prepend);
        }

        if($shippingMethod === false){
            return $options;
        }else{
            return isset($options[$shippingMethod])?$options[$shippingMethod]:'';
        }
    }


    /**
     * 产品类型
     * @param $type
     * @return array|string
     */
    public static function productTypes($type=false){
        $options = [
            'factory' =>'工厂款',
            'taobao'  => '淘宝款',
            'virtual' => '虚拟款',
        ];
        if($type === false){
            return $options;
        }else{
            return isset($options[$type])?$options[$type]:'';
        }
    }

    /**
     * 戒指的男女款类型
     * @param bool $type
     * @return array|string
     */
    public static function ringTypes($type = false){
        $options = [
            'none' =>'无',
            'men'  => '男款',
            'women' => '女款',
        ];
        if($type === false){
            return $options;
        }else{
            return isset($options[$type])?$options[$type]:'';
        }
    }

    /**
     * 发货状态
     * @param bool $type
     * @return array|string
     */
    public static function shipmentTypes($type = false){
        $options = [
            'normal' =>'正常',
            'address_wrong'  => '错误',
        ];
        if($type === false){
            return $options;
        }else{
            return isset($options[$type])?$options[$type]:'';
        }
    }

    /**
     * 备注类型
     * @param bool $type
     * @return array|string
     */
    public static function commentTypes($type = false){
        $options = [
            Comment::COMMENT_TYPE_CHANGE_ADDRESS  => '修改收货地址',
            Comment::COMMENT_TYPE_CHANGE_SHIPPING_METHOD  => '修改邮寄方式',
            Comment::COMMENT_TYPE_ORDER_PAUSE  => '订单待定',
            Comment::COMMENT_TYPE_ORDER_CANCEL  => '订单取消',
            Comment::COMMENT_TYPE_ORDER_EXPEDITE  => '订单加急',
            Comment::COMMENT_TYPE_ORDER_EXPEDITE  => '订单加急',
            Comment::COMMENT_TYPE_ORDER_RETURN_EXCHANGE  => '退换货',
            Comment::COMMENT_TYPE_OTHERS  => '其他',
            Comment::COMMENT_TYPE_CHANGE_PRODUCT  => '修改产品SKU',
            Comment::COMMENT_TYPE_CHANGE_PRODUCT_SIZE  => '修改产品尺码',
            Comment::COMMENT_TYPE_CHANGE_PRODUCT_NUMBER  => '修改产品数量',
            Comment::COMMENT_TYPE_CHANGE_PRODUCT_ENGRAVINGS =>'修改刻字',
            Comment::COMMENT_TYPE_CHANGE_GRAND_PRICE =>'产品总价',
            Comment::COMMENT_TYPE_EXPEDITE =>'加急',
            Comment::COMMENT_TYPE_CANCEL =>'取消',
            Comment::COMMENT_TYPE_DESCRIPTION =>'说明',
        ];
        if($type === false){
            return $options;
        }else{
            return isset($options[$type])?$options[$type]:'';
        }
    }

    /**
     * 所有的订单类型
     * @param $status
     * @return array|string
     */
    public static function orderTypes($type=false){
        $options = [
            'custom' =>'定制单',
            'stock'  => '库存单',
            'taobao'  => '淘宝单',
            'stockup'  => '补库存单',
        ];
        if($type === false){
            return $options;
        }else{
            return isset($options[$type])?$options[$type]:'';
        }
    }

    /**
     * 次品状态
     * @param $status
     * @return array|string
     */
    public static function rejectsStatus($status=false){
        $options = [
            'rejected' =>'次品',
            'solved'  => '已解决',
        ];
        if($status === false){
            return $options;
        }else{
            return isset($options[$status])?$options[$status]:'';
        }
    }

    public static function paymentMethods($method = false){
        $options = [
            'globalcollect_cc_merchant' =>'Global Collect',
            'paypal_express'  => 'Paypal',
            'masapi'  => 'MASAPAY',
            'purchaseorder'  => 'Purchase Order',
        ];
        if($method === false){
            return $options;
        }else{
            return isset($options[$method])?$options[$method]:'';
        }
    }

    /**
     * 客户端
     * @param bool $type
     * @return array|string
     */
    public static function clients($type = false,$prepend=false){
        $options = [
            'pc' =>'PC',
            'mobile'  => 'Mobile',
        ];
        if($prepend){
            array_unshift($options,$prepend);
        }
        if($type === false){
        return $options;
        }else{
            return isset($options[$type])?$options[$type]:'';
        }
    }

    /**
     * 网站选项
     * @param bool $code
     * @return array|string
     */
    public static function websiteOptions($website=false,$prepend=false){
        $options = [
            'us'    => 'US',
            'za'    => 'ZA',
            'au'    => 'AU',
            'nz'    => 'NZ',
            'gb'    => 'GB',
            'it'    => 'IT',
            'ae'    => 'AE',
            'SYS'    => 'SYS',

        ];
        if($prepend){
            array_unshift($options,$prepend);
        }
        if($website === false){
            return $options;
        }else{
            return isset($options[$website])?$options[$website]:'';
        }
    }

    /**
     * 所有的订单类型
     * @param $status
     * @return array|string
     */
    public static function ringCategory($type=false){
        $options = [
            '0' =>'单戒',
            'stock'  => '库存单',
            'stockup'  => '补库存单',
        ];
        if($type === false){
            return $options;
        }else{
            return isset($options[$type])?$options[$type]:'';
        }
    }

    /**
     * 电镀颜色
     * @param $status
     * @return array|string
     */
    public static function electroplatingColor($color=false,$prepend=false){
        $options = [
            'white' =>'白色',
            'black'  => '黑色',
            'gold'  => '金色',
            'rose_gold'  => '玫瑰金',
            'multi'  => '分色',
        ];
        if($prepend){
            array_unshift($options,$prepend);
        }
        if($color === false){
            return $options;
        }else{
            return isset($options[$color])?$options[$color]:'';
        }
    }

    /**
     * YesNo Option
     * @param $status
     * @return array|string
     */
    public static function yesNoOptions($type=false,$prepend=false){
        $options = [
            '1' =>'是',
            '0'  => '否',
        ];

        if($prepend){
            array_unshift($options,$prepend);
        }

        if($type === false){
            return $options;
        }else{
            return isset($options[$type])?$options[$type]:'';
        }
    }

    /**
     * YesNo Option
     * @param $status
     * @return array|string
     */
    public static function serviceIssueFromOptions($type=false){
        $options = [
            'email' =>'邮件',
            'tel'  => '电话',
            'facebook'  => 'Facebook',
            'paypal'  => 'Paypal',
            'chat'  => 'Chat',
        ];
        if($type === false){
            return $options;
        }else{
            return isset($options[$type])?$options[$type]:'';
        }
    }

    /**
     * YesNo Option
     * @param $status
     * @return array|string
     */
    public static function issueStatusOptions($type=false){
        $options = [
            'solved' =>'解决',
            'pending'  => '待解决',
        ];
        if($type === false){
            return $options;
        }else{
            return isset($options[$type])?$options[$type]:'';
        }
    }

    public static function templateOptions($status=false){
        $options = [
            '全局状态'=>[
                ProductTemplate::STATUS_PENDING => '等待确认',
                ProductTemplate::STATUS_CANCELLED => '已取消',
                ProductTemplate::STATUS_FINISHED => '流程结束'
            ],
            '工厂状态'=>[
                ProductTemplate::STATUS_FACTORY_PENDING => '等待开版',
                ProductTemplate::STATUS_FACTORY_START => '等待电绘',
                ProductTemplate::STATUS_FACTORY_STEP_ONE => '等待银版',
                ProductTemplate::STATUS_FACTORY_STEP_TWO => '等待压模',
                ProductTemplate::STATUS_FACTORY_STEP_THREE => '等待开版完成',
            ],
            '渲染状态'=>[
                ProductTemplate::STATUS_STUDIO_PENDING => '等待渲染',
                ProductTemplate::STATUS_STUDIO_START => '渲染中',
                ProductTemplate::STATUS_STUDIO_END => '渲染结束',
                ProductTemplate::STATUS_REPAIR_PENDING => '等待返修',
                ProductTemplate::STATUS_REPAIR => '返修中',
                ProductTemplate::STATUS_ACCEPTED_PENDING => '等待最终验收',
                ProductTemplate::STATUS_CONFIRM_PENDING => '等待工厂验收',
            ]

        ];
        if($status === false){
            return $options;
        }else{
            $newOptions = [];
            foreach($options as $option){
                $newOptions = array_merge($newOptions, $option);
            }

            return isset($newOptions[$status])?$newOptions[$status]:'';
        }
    }

    public static function templateTypes($type=false){
        $options = [
            '0' =>'新版',
            '1'  => '变体',
        ];
        if($type === false){
            return $options;
        }else{
            return isset($options[$type])?$options[$type]:'';
        }
    }

    public static function templateCategory($type=false){
        $options = ['2in1'=>'2合1（副戒不带主石）','multi'=>'套戒','couple'=>'对戒','single'=>'单戒','band'=>'Band','necklace'=>'项链','bracelet'=>'手链','original'=>'原创'];
        if($type === false){
            return $options;
        }else{
            return isset($options[$type])?$options[$type]:'';
        }
    }

    public static function countryOptions($country = false,$prepend=false){
        $qb = new Query();
        $options = $qb->select("country_id")->from("order_address")->distinct()->indexBy("country_id")->column();

        if($prepend){
            array_unshift($options,$prepend);
        }
        if($country === false){
            return $options;
        }else{
            return isset($options[$country])?$options[$country]:'';
        }
    }

    public static function paymentStatusOptions($status = false,$prepend=false){
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

        if($prepend){
            array_unshift($options,$prepend);
        }

        if($status === false){
            return $options;
        }else{
            return isset($options[$status])?$options[$status]:'';
        }
    }



    /**
     * 订单类型
     */
    public static function orderTypeOptions($status=false,$prepend=false){
        $options = [
            Order::ORDER_TYPE_CUSTOM    => '定制单',
            Order::ORDER_TYPE_STOCK     => '库存单',
            Order::ORDER_TYPE_TB        => '淘宝单',
            Order::ORDER_TYPE_MIXTURE   => '混合单',
        ];
        if($prepend){
            array_unshift($options,$prepend);
        }
        if($status === false){
            return $options;
        }else{
            return isset($options[$status])?$options[$status]:'';
        }
    }

    /**
     * 耗材类型
     */
    public static function materialTypeOptions($status=false,$prepend=false){
        $options = [
            'drawer_single'     => '抽屉盒-单孔',
            'drawer_sets'       => '抽屉盒-单孔(套/对戒)',
            'drawer_double'     => '抽屉盒-双孔',
            'bag'               => '手提袋',
            'duster'            => '擦银布',
            'card'              => '卡片',
            'ring_box_single'   => '单戒盒',
            'ring_box_sets'     => '套/对戒盒',
            'necklace_box'      => '手/项链',
        ];
        if($prepend){
            array_unshift($options,$prepend);
        }
        if($status === false){
            return $options;
        }else{
            return isset($options[$status])?$options[$status]:'';
        }
    }

    public static function colorCards($color=false,$prepend=false){
        $options = [
                'A1lvbo'=>['label'=>'A1绿玻','position-y'=>'0'],
                'A2baolan'=>['label'=>'A2宝蓝','position-y'=>'50'],
                'A3hailan'=>['label'=>'A3海蓝','position-y'=>'100'],
                'A4shenlan'=>['label'=>'A4深蓝','position-y'=>'150'],
                'A5jianjing'=>['label'=>'A5尖晶','position-y'=>'200'],
                'A7zihongbo'=>['label'=>'A7紫红玻','position-y'=>'250'],
                'A8dahong'=>['label'=>'A8大红','position-y'=>'300'],
                'A9baibo'=>['label'=>'A9白玻','position-y'=>'350'],
                'A10heibo'=>['label'=>'A10黑玻','position-y'=>'400'],
                'A12meiguihong5'=>['label'=>'A12玫瑰红5#','position-y'=>'450'],
                'A19ganlanlv'=>['label'=>'A19橄榄绿','position-y'=>'500'],
                'A20zhenzhu1'=>['label'=>'A20珍珠1','position-y'=>'550'],
                'A21zhenzhu2'=>['label'=>'A21珍珠2','position-y'=>'600'],
                'A22ruishilanbo'=>['label'=>'A22瑞士兰玻','position-y'=>'650'],
                'A24ganlanlvbo'=>['label'=>'A24橄榄绿玻','position-y'=>'700'],
                'A29heijianjing'=>['label'=>'A29黑尖晶','position-y'=>'750'],
                'A30lannami'=>['label'=>'A30蓝纳米','position-y'=>'800'],

                'B1baigao'=>['label'=>'B1白锆','position-y'=>'852'],
                'B2heigao'=>['label'=>'B2黑锆','position-y'=>'902'],
                'B3fenhong1'=>['label'=>'B3粉红（1）','position-y'=>'952'],
                'B4fenhong2'=>['label'=>'B4粉红（2）','position-y'=>'1002'],
                'B7shenhuanjin'=>['label'=>'B7深金黄','position-y'=>'1052'],
                'B8zhongjinhuang'=>['label'=>'B8中金黄','position-y'=>'1102'],
                'B9qianjinhuang'=>['label'=>'B9浅金黄','position-y'=>'1152'],
                'B10shenxiangbin'=>['label'=>'B10深香槟','position-y'=>'1202'],
                'B11xiangbin'=>['label'=>'B11香槟','position-y'=>'1252'],
                'B12qianxiangbin'=>['label'=>'B12浅香槟','position-y'=>'1302'],
                'B13ehuang'=>['label'=>'B13鹅黄','position-y'=>'1354'],
                'B15shenzihong'=>['label'=>'B15深紫红','position-y'=>'1404'],
                'B16zihong'=>['label'=>'B16紫红','position-y'=>'1454'],
                'B17qianzihong'=>['label'=>'B17浅紫红','position-y'=>'1504'],
                'B18ganlanhuang'=>['label'=>'B18橄榄黄','position-y'=>'1556'],
                'B19shenjuhong'=>['label'=>'B19深桔红','position-y'=>'1606'],
                'B20juhong'=>['label'=>'B20桔红','position-y'=>'1656'],
                'B21shenshiliuhong'=>['label'=>'B21深石榴红','position-y'=>'1706'],
                'B22shiliuhong'=>['label'=>'B22石榴红','position-y'=>'1756'],
                'B23zilan'=>['label'=>'B23紫蓝','position-y'=>'1806'],
                'B24xianzilan'=>['label'=>'B24浅紫蓝','position-y'=>'1856'],
                'B25ganlanlv1'=>['label'=>'B25橄榄绿（1）','position-y'=>'1906'],
                'B26ganlanlv2'=>['label'=>'B26橄榄绿（2）','position-y'=>'1956'],
                'B27biansegao'=>['label'=>'B27变色锆','position-y'=>'2006'],
                'B28shenlangao'=>['label'=>'B28海蓝锆','position-y'=>'2056'],
                'B29lvgao'=>['label'=>'B29绿锆','position-y'=>'2108'],
                'B34honggangyu3'=>['label'=>'B34红刚玉3#','position-y'=>'2158'],
                'B35honggangyu5'=>['label'=>'B35红刚玉5#','position-y'=>'2208'],
                'B37jianjing120'=>['label'=>'B37尖晶120#','position-y'=>'2258'],
                'B38jianjing113'=>['label'=>'B38尖晶113#','position-y'=>'2308'],
                'B39jianjing114'=>['label'=>'B39尖晶114#','position-y'=>'2358'],
                'B40jianjing106'=>['label'=>'B40尖晶106#','position-y'=>'2410'],
                'B41ruishilan'=>['label'=>'B41瑞士兰','position-y'=>'2460'],
                'B42lvjianjing'=>['label'=>'B42绿尖晶','position-y'=>'2510'],
                'B43langang34'=>['label'=>'B43 34#兰刚','position-y'=>'2560'],
                'B45yangao'=>['label'=>'B45烟锆','position-y'=>'2610'],
        ];

        if($prepend){
            array_unshift($options,$prepend);
        }
        if($color === false){
            return $options;
        }else{
            return isset($options[$color])?$options[$color]:'';
        }
    }
}