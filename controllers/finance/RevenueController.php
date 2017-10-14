<?php
/**
 * LoanController.php 
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/7/15
 */

namespace app\controllers\finance;


use app\controllers\BaseController;
use app\models\Comment;
use app\models\order\Revenue;
use app\models\product\ProductDelivered;
use app\models\Website;
use renk\yiipal\helpers\ArrayHelper;

class RevenueController extends BaseController{
    public function actionIndex(){
        if($this->isPost()) {
            $dateRange = $this->post('date_range', false);
            $startTime = strtotime(date("Y-m-d"));
            $endTime = strtotime(date("Y-m-d") . " +1 day");
            if ($dateRange) {
                $queryDate = explode("/", $dateRange);
                $startTime = strtotime($queryDate[0]);
                $endTime = strtotime($queryDate[1] . " +1 day");
            }

            $paymentMethod = $this->post('payment_method');
            $client = $this->post('client');
            $source = $this->post('source');
            $revenue = new Revenue();
            $data = $revenue->getRevenue($startTime,$endTime,$paymentMethod,$client,$source);
            return $this->json_output(['data'=>$data]);
        }else{
            return $this->render('index');
        }
    }
}