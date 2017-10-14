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
use app\models\count\CountOrder;
use app\models\order\Item;
use renk\yiipal\components\ExportData;
use yii\db\Expression;
use renk\yiipal\helpers\FileHelper;
use yii;

class OrderController extends BaseController{

    public function actionIndex(){
        $searchModel = new CountOrder();
        $data = $searchModel->getCountOrder(Yii::$app->request->queryParams);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'data' => $data,
        ]);
    }
    
    /**
     * 导出订单统计
     * @return array
     */
    public function actionExport(){
        $searchModel = new CountOrder();
        $data = $searchModel->getCountOrder(Yii::$app->request->queryParams);
        
        $header = $this->createExportHeader();
        $data = $this->formatExportData($data);
        $objExportData = new ExportData($header,$data);
        $objExportData->createExcel($header,$data,20);
        $path = 'download/orderCount/'.date("Y-m-d").'/'.date('Y-m-d-H-i-s').'.xls';
        $objExportData->saveFileTo($path);
        return $this->json_output(['data'=>['/'.$path]]);
    }
    
    /**
     * 导出的头部
     * @return array
     */
    private function createExportHeader(){
        $header = [
            '订单统计',
            ['value'=>'移动端','col'=>1],
            '',
            ['value'=>'PC端','col'=>10],
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            ['value'=>'整站','col'=>2],
            '',
            '',
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
        
        $output[$i][] = '日期';
        $output[$i][] = '总订单';
        $output[$i][] = 'Paypal支付';
        $output[$i][] = '总订单';
        $output[$i][] = '已支付';
        $output[$i][] = 'Affirm支付';
        $output[$i][] = 'Affirm支付成功率';
        $output[$i][] = 'Paypal支付';
        $output[$i][] = 'Paypal支付成功率';
        $output[$i][] = 'Pending订单';
        $output[$i][] = 'Affirm Pending';
        $output[$i][] = 'Paypal Pending';
        $output[$i][] = '其他订单';
        $output[$i][] = 'PC支付率';
        $output[$i][] = '总订单';
        $output[$i][] = '已支付';
        $output[$i][] = '支付率';
        
        foreach($items as $index=>$item){
            $i++;
            $output[$i][] = $index;
            $output[$i][] = $item['mobile_order_total'];
            $output[$i][] = $item['mobile_payment_status_processing'];
            $output[$i][] = $item['pc_order_total'];
            $output[$i][] = $item['pc_payment_status_processing'];
            $output[$i][] = $item['pc_payment_method_affirm_processing'];
            $output[$i][] = $item['pc_affirm_rate'];
            $output[$i][] = $item['pc_payment_method_paypal_express_processing'];
            $output[$i][] = $item['pc_paypal_express_rate'];
            $output[$i][] = $item['pc_payment_status_pending'];
            $output[$i][] = $item['pc_payment_method_affirm_pending'];
            $output[$i][] = $item['pc_payment_method_paypal_express_pending'];
            $output[$i][] = $item['pc_order_other'];
            $output[$i][] = $item['pc_processing_rate'];
            $output[$i][] = $item['order_total'];
            $output[$i][] = $item['order_total_processing'];
            $output[$i][] = $item['processing_rate'];
        }
        return $output;
    }
}