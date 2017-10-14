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
use app\models\order\Item;
use app\models\order\Order;
use yii\helpers\ArrayHelper;

class CommonHelper {
    /**
     * 工厂状态选项
     */
    public static function getStocksBySize($stocks, $size){
        $stocks = ArrayHelper::index($stocks, 'size_code');

    }

    public static function filterEmptyStr($str){
        if(empty($str) || in_array(strtolower($str),['null'])){
            return '';
        }else{
            return $str;
        }
    }

    /**
     * 检查Order是否可以编辑
     * @param $model
     * @return bool
     */
    public static function canEditItem($model){
        if(!in_array($model->order->payment_status,['processing','complete'])||in_array($model->item_status,['cancelled','shipped','complete','return_completed_part','return_completed']) || in_array($model->order->status,['cancelled'])){
            return false;
        }else{
            return true;
        }
    }

    //检查Order编辑条件
    public static function canEditOrder($model){
        if(!in_array($model->payment_status,['processing','complete'])||in_array($model->status,['cancelled','shipped','complete','return_completed_part','return_completed'])){
            return false;
        }else{
            return true;
        }
    }

    /**
     * 获取两个日期之间的所有日期，不包含最后一个日期.
     *
     * @param $start
     * @param $end
     * @return array
     */
    public static function getDatePeriod($start, $end,$interval='P1D',$format="Y-m-d"){
        $cacheKey = $start.$end.$interval.$format;
        static $cache = [];
        if(isset($cache[$cacheKey])){
            return $cache[$cacheKey];
        }
        $period = new \DatePeriod(
            new \DateTime($start),
            new \DateInterval($interval),
            new \DateTime($end)
        );
        $dates = [];
        foreach ($period as $date) {
            $dates[] = $date->format($format);
        }
        if(empty($dates)){
            $dates[] = date($format,strtotime($start));
        }
        $cache[$cacheKey] = $dates;
        return $dates;
    }

    /**
     * 获取日期区间
     * @param $dateList
     * @param string $viewType
     * @return array
     */
    public static function getDateRanges($date,$viewType='day'){
        $startDate = date("y-m-01");
        $endDate = date("y-m-d");
        if(!empty($date)){
            $date = explode("/", $date);
            $startDate = $date[0];
            $endDate = $date[1];
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

    /**
     * 获取两个日期之间的间隔天数
     * @param $date1
     * @param $date2
     * @param string $format
     * @return string
     */
    public static function getDateDiff($date1, $date2,$format='%a'){
        $interval =date_diff(date_create($date1),date_create($date2));
        return $interval->format($format);
    }

    /**
     * 获取对应尺码的库存数据
     * @param $stocks
     * @param $size
     * @param string $sizeType
     * @return array
     */
    public static function getStocksBy($stocks,$size,$sizeType='none'){
        $output = ['actual_total'=>0,'virtual_total'=>0];
        foreach($stocks as $item){
            if($item['size_code'] == $size && $item['type'] == $sizeType){
                $output = $item;
                break;
            }
        }
        return $output;
    }

    /**
     * 分类日期段
     * @param $dateRange
     * @return array
     */
    public static function splitDateRange($dateRange){
        $output = ['start'=>0,'end'=>0];
        $date = explode("/", $dateRange);
        if(count($date)==1){
            $output['start'] = strtotime($dateRange);
        }else{
            $output['start'] = strtotime($date[0].' 00:00:00');
            $output['end'] = strtotime($date[1]." 23:59:59");
        }
        return $output;
    }


    /**
     * 获取产品分类
     * @param $cid
     * @return string
     */
    public static function checkProductCategory($cid){
        $type = '';
        switch($cid){
            case 3:
                $type = 'rings';
                break;
            case 4:
                $type = 'necklace';
                break;
            case 5:
                $type = 'bracelet';
                break;
            default:
                $type = 'rings';

        }
        return $type;
    }

    /**
     * 根据备注获取订单状态修改历史
     * @param $comments
     * @return string
     */
    public static function getOrderChangeHistory($comments){
        if(empty($comments)){
            return '';
        }
        $subjects = [
            Comment::COMMENT_TYPE_CHANGE_ADDRESS,
            Comment::COMMENT_TYPE_CHANGE_PRODUCT,
            Comment::COMMENT_TYPE_CHANGE_PRODUCT_ENGRAVINGS,
            Comment::COMMENT_TYPE_CHANGE_PRODUCT_NUMBER,
            Comment::COMMENT_TYPE_CHANGE_PRODUCT_SIZE,
            Comment::COMMENT_TYPE_CHANGE_SHIPPING_METHOD,
        ];
        $output = '';
        foreach($comments as $comment){
            if(in_array($comment->subject,$subjects)){
                $output .= '<span class="comment-type-span">'.Options::commentTypes($comment->subject).'</span> | ';
            }
        }
        return $output;
    }

    /**
     * 检查备注是否已读
     * @param $comments
     * @return bool
     */
    public static function checkHasRead($comments){
        $uid = \Yii::$app->user->id;
        if(empty($comments)){
            return true;
        }
        $flag = true;
        foreach($comments as $comment){
            $readUids = $comment->read_uids;
            $readUids = explode(",",$readUids);
            if(!in_array($uid, $readUids)){
                $flag = false;
                break;
            }
        }
        return $flag;
    }


    /**
     * 合并图片
     * @param $imgl
     * @param $img2
     * @param $fileName
     */
    public static function addLogoToLabel($logo, $image, $fileName){

        list($logo_width, $logo_height) = getimagesize($logo);
        list($image_width, $image_height) = getimagesize($image);

        $canvas = imagecreate(1400, 800);

        $logoData = imagecreatefromgif($logo);
        imagecopyresampled($canvas, $logoData, 0, 0,
            0, 0, 200, 800, $logo_width, $logo_height);

        $imageData = imagecreatefromgif($image);
        imagecopyresampled($canvas, $imageData, 200, 0,
            0, 0, 1200, 800, $image_width, $image_height);
        imagegif($canvas, $fileName);
        imagedestroy($logoData);
        imagedestroy($imageData);
        imagedestroy($canvas);
    }

    /**
     * 美国州转缩写
     * @param $region
     * @return string
     */
    public static function getRegionShortName($region){
        $region = strtolower($region);
        $regionMap = [
            "alabama"=>"AL",
            "alaska"=>"AK",
            "arizona"=>"AZ",
            "arkansas"=>"AR",
            "california"=>"CA",
            "colorado"=>"CO",
            "connecticut"=>"CT",
            "delaware"=>"DE",
            "florida"=>"FL",
            "georgia"=>"GA",
            "hawaii"=>"HI",
            "idaho"=>"ID",
            "illinois"=>"IL",
            "indiana"=>"IN",
            "iowa"=>"IA",
            "kansas"=>"KS",
            "kentucky"=>"KY",
            "louisiana"=>"LA",
            "maine"=>"ME",
            "maryland"=>"MD",
            "massachusetts"=>"MA",
            "michigan"=>"MI",
            "minnesota"=>"MN",
            "mississippi"=>"MS",
            "missouri"=>"MO",
            "montana"=>"MT",
            "nebraska"=>"NE",
            "nevada"=>"NV",
            "new hampshire"=>"NH",
            "new jersey"=>"NJ",
            "new mexico"=>"NM",
            "new york"=>"NY",
            "north carolina"=>"NC",
            "north dakota"=>"ND",
            "ohio"=>"OH",
            "oklahoma"=>"OK",
            "oregon"=>"OR",
            "pennsylvania"=>"PA",
            "rhode island"=>"RL",
            "south carolina"=>"SC",
            "south dakota"=>"SD",
            "tennessee"=>"TN",
            "texas"=>"TX",
            "utah"=>"UT",
            "vermont"=>"VT",
            "virginia"=>"VA",
            "washington"=>"WA",
            "west virginia"=>"WV",
            "wisconsin"=>"WI",
            "wyoming"=>"WY"
        ];
        if(isset($regionMap[$region])){
            return $regionMap[$region];
        }else{
            return $region;
        }
    }
    
    public static function number2Percent($number,$percision=2){
        if($number == 0){
            return 0;
        } else {
            return round($number*100,$percision).'%';
        }
    }
}