<?php
/**
 * TemplateController.php 
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/8/11
 */

namespace app\controllers\product;

use app\models\product\Product;
use renk\yiipal\components\ExportData;
use renk\yiipal\helpers\FileHelper;
use Yii;
use app\controllers\BaseController;
use yii\web\UploadedFile;

class ExportController extends BaseController{
    /**
     * 根据SKU列表导出产品列表
     * @return string
     */
    public function actionSku()
    {
        $productModel = new Product();
        if (Yii::$app->request->post()) {
            $files = UploadedFile::getInstances($productModel, 'files');
            if(empty($files)){
                $productModel->addError('files', '请上传CSV文件!');
                
                return $this->render('sku',[
                    'model'=>$productModel,
                    'path'=>''
                ]);
                
            }
            
            $file = $files[0];
            
            if (($handle = fopen($file->tempName, "r")) === FALSE) {
                $productModel->addError('files', '导入失败');
                return $this->render('sku',[
                    'model'=>$productModel,
                    'path'=>''
                ]);
            }
            
            $header = ['图片'];
            $_data = [];
            $sku_column = 0;
            $_sku = [];
            $i = 0;
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if( !$data ) {
                    continue;
                }
                $data = eval('return '.iconv('gbk','utf-8',var_export($data,true)).';');
                if($i == 0){
                    foreach($data as $column=>$column_name){
                        if(strtolower($column_name) == 'sku'){
                            $sku_column = $column;
                        }
                        $header[] = $column_name;
                    }
                } else {
                    $_data[] = $data;
                    $_sku[] = $data[$sku_column];
                }
                $i++;
            }
            fclose($handle);
            
            $products = [];
            $results = $productModel->getExportProductsBySku($_sku);
            foreach($results as $result){
                $products[$result['sku']] = $result;
            }
            
            $data = [];
            foreach($_data as $index=>$row){
                $row_sku = trim($row[$sku_column]);
                $filePath = isset($products[$row_sku]) ? $products[$row_sku] : '';
                if($filePath && $filePath['file_path']){
                    $filePath = FileHelper::getThumbnailPath($filePath['file_path'], '300x300');
                    $filePath = str_replace(urlencode("#"),"#",$filePath);
                    $data[$index][] = ['type'=>'image','value'=>'./'.$filePath];
                } else {
                    $data[$index][] = '';
                }
                $data[$index] = array_merge($data[$index],$row);
            }
            
            $objExportData = new ExportData($header,$data);
            $objExportData->createExcel($header,$data);
            $path = 'download/exportProduct/'.date("Y-m-d").'/'.'sku-'.date('Y-m-d-H-i-s').'.xls';
            $objExportData->saveFileTo($path);
            //return $this->redirect(['/'.$path]);
            //return $this->json_output(['data'=>['/'.$path]]);
            return $this->render('sku',[
                'model'=>$productModel,
                'path'=>'/'.$path
            ]);
        } else {
            return $this->render('sku',[
                'model'=>$productModel,
                'path'=>''
            ]);
        }
    }
    
}