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
use app\models\count\Top10;
use renk\yiipal\components\ExportData;
use renk\yiipal\helpers\FileHelper;
use yii;

class Top10Controller extends BaseController{

    public function actionIndex(){
        $searchModel = new Top10();
        $data = $searchModel->getTop10(Yii::$app->request->queryParams);
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
        $searchModel = new Top10();
        $data = $searchModel->getTop10(Yii::$app->request->queryParams);
        
        $header = $this->createExportHeader();
        $data = $this->formatExportData($data);
        $objExportData = new ExportData($header,$data);
        $objExportData->createExcel($header,$data);
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
            '',
        ];
        $model = new Top10();
        foreach($model->select_magento_cid as $magento_cid_name){
            $header[] = ['value'=>$magento_cid_name,'col'=>2];
            $header[] = '';
            $header[] = '';
        }
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
        
        $output[$i][] = '';
        
        $model = new Top10();
        foreach($model->select_magento_cid as $magento_cid_name){
            $output[$i][] = 'SKU';
            $output[$i][] = '图片';
            $output[$i][] = '数量';
        }
        
        foreach($items as $index=>$item){
            $i++;
            $output[$i][] = $item['top'];
            
            $j=0;
            foreach($model->select_magento_cid as $magento_cid_name){
                $output[$i][] = $item['sku'.$j];
                
                if(!empty($item['image'.$j])){
                    $filePath = FileHelper::getThumbnailPath($item['image'.$j], '300x300');
                    $filePath = str_replace(urlencode("#"),"#",$filePath);
                    $output[$i][] = ['type'=>'image','value'=>'./'.$filePath];
                } else {
                    $output[$i][] = '';
                }
                
                $output[$i][] = $item['count'.$j];
                $j++;
            }
        }
        return $output;
    }
}