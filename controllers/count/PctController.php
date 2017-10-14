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
use app\models\count\Pct;
use renk\yiipal\components\ExportData;
use yii;

class PctController extends BaseController{

public function actionIndex(){
        $searchModel = new Pct();
        $data = $searchModel->getPct(Yii::$app->request->queryParams);
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
        $searchModel = new Pct();
        $data = $searchModel->getPct(Yii::$app->request->queryParams);
        
        $header = $this->createExportHeader();
        $data = $this->formatExportData($data);
        $objExportData = new ExportData($header,$data);
        $objExportData->createExcel($header,$data,20);
        $path = 'download/pct/'.date("Y-m-d").'/'.date('Y-m-d-H-i-s').'.xls';
        $objExportData->saveFileTo($path);
        return $this->json_output(['data'=>['/'.$path]]);
    }
    
    /**
     * 导出的头部
     * @return array
     */
    private function createExportHeader(){
        $header = [
            '客单价统计',
            ['value'=>'移动端','col'=>2],
            '',
            '',
            ['value'=>'PC端','col'=>2],
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
        
        $output[$i][] = '订单数';
        $output[$i][] = '销售总额';
        $output[$i][] = '客单价';
        
        $output[$i][] = '订单数';
        $output[$i][] = '销售总额';
        $output[$i][] = '客单价';
        
        $output[$i][] = '订单数';
        $output[$i][] = '销售总额';
        $output[$i][] = '客单价';

        foreach($items as $index=>$item){
            $i++;
            $output[$i][] = $index;
            
            $output[$i][] = $item['mobile_order_count'];
            $output[$i][] = $item['mobile_order_grand_total'];
            $output[$i][] = $item['mobile_pct'];
            
            $output[$i][] = $item['pc_order_count'];
            $output[$i][] = $item['pc_order_grand_total'];
            $output[$i][] = $item['pc_pct'];
            
            
            $output[$i][] = $item['all_order_count'];
            $output[$i][] = $item['all_order_grand_total'];
            $output[$i][] = $item['all_pct'];
            
        }
        return $output;
    }
}