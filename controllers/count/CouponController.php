<?php
/**
 * ProductController.php 
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/9/18
 */

namespace app\controllers\count;

use app\controllers\BaseController;
use app\models\count\CountCoupon;
use renk\yiipal\components\ExportData;
use yii;

class CouponController extends BaseController{

    public function actionIndex(){
        $searchModel = new CountCoupon();
        $data = $searchModel->getCountCoupon(Yii::$app->request->queryParams);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'data' => $data,
        ]);
    }
    
    /**
     * 导出数据
     * @return array
     */
    public function actionExport(){
        $searchModel = new CountCoupon();
        $data = $searchModel->getCountCoupon(Yii::$app->request->queryParams);
        
        $header = $this->createExportHeader();
        $data = $this->formatExportData($data);
        $objExportData = new ExportData($header,$data);
        $objExportData->createExcel($header,$data,20);
        $path = 'download/couponCount/'.date("Y-m-d").'/'.date('Y-m-d-H-i-s').'.xls';
        $objExportData->saveFileTo($path);
        return $this->json_output(['data'=>['/'.$path]]);
    }
    
    /**
     * 导出的头部
     * @return array
     */
    private function createExportHeader(){
        $header = [
            '日期',
            'coupon',
            '总使用量',
            'M使用量',
            'PC使用量',
            'coupon使用量占比',
            'coupon量与总订单占比',
            '总订单数',
            'M订单数',
            'PC订单数',
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
        $i=0;
        $count = 0;
        foreach($items as $index=>$item){
            $start = $i;
            foreach ($item as $coupon=>$_item){
                if($start == $i){
                    $output[$i][] = ['value'=>$index,'row'=>count($item)-1];
                } else {
                    $output[$i][] = $index;
                }
                $output[$i][] = $_item['coupon_code'];
                $output[$i][] = $_item['used'];
                $output[$i][] = $_item['used_mobile'];
                $output[$i][] = $_item['used_pc'];
                $output[$i][] = $_item['used_rate_percent'];
                $output[$i][] = $_item['total_rate_percent'];
                if($start == $i){
                    $output[$i][] = ['value'=>$_item['total'],'row'=>count($item)-3];
                    $output[$i][] = ['value'=>$_item['total_mobile'],'row'=>count($item)-3];
                    $output[$i][] = ['value'=>$_item['total_pc'],'row'=>count($item)-3];
                } else {
                    $output[$i][] = $_item['total'];
                    $output[$i][] = $_item['total_mobile'];
                    $output[$i][] = $_item['total_pc'];
                }
                $i++;
            }
            
            $output[$i][] = '';
            $output[$i][] = '';
            $output[$i][] = '';
            $output[$i][] = '';
            $output[$i][] = '';
            $output[$i][] = '';
            $output[$i][] = '';
            $output[$i][] = '';
            $output[$i][] = '';
            $output[$i][] = '';
            $i++;
            
            $count++;
            if($count < count($items)){
                $output[$i] = $this->createExportHeader();
                $i++;
            }
        }
        return $output;
    }
}