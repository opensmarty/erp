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
use app\models\product\ProductDelivered;

class LoanController extends BaseController{
    public function actionIndex(){
        if($this->isPost()) {
            $data = ['qty_ordered'=>[],'qty_passed'=>[]];
            $dateRange = $this->post('date_range', false);
            $startTime = strtotime(date("Y-m-d"));
            $endTime = strtotime(date("Y-m-d") . " +1 day");
            if ($dateRange) {
                $queryDate = explode("/", $dateRange);
                $startTime = strtotime($queryDate[0]);
                $endTime = strtotime($queryDate[1] . " +1 day");
            }
            $productDelivery = new ProductDelivered();
            $data = $productDelivery->getReportInfo($startTime,$endTime);
            return $this->json_output(['data'=>$data]);
        }else{
            return $this->render('index');
        }
    }
}