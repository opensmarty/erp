<?php
/**
 * ExportData.php 
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/5/18
 */

namespace renk\yiipal\components;


use Faker\Provider\Image;
use renk\yiipal\helpers\FileHelper;
use yii\base\InvalidParamException;

class ExportData {
    private $objPHPExcel = null;
    private $cells = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z',
                      'AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ','BA','BB','BC','BD',
                     ];
    public function __construct(){
        $this->objPHPExcel = new \PHPExcel();
    }

    /**
     * 初始化
     */
    private function init(){
        error_reporting(0);
        // Set document properties
        $this->objPHPExcel->getProperties()->setCreator("Jeulia ERP2.0")
            ->setLastModifiedBy("Jeulia ERP2.0")
            ->setTitle("Jeulia ERP2.0 Export File")
            ->setSubject("")
            ->setDescription("")
            ->setKeywords("")
            ->setCategory("Jeulia ERP2.0 Export File");
    }

    /**
     * 创建Excel
     * @param array $header
     * @param array $data
     * @param int $rowHeight
     * @throws \PHPExcel_Exception
     */
    public function createExcel($header=[],$data=[],$rowHeight = 80,$sheetTitle='Worksheet',$sheetIndex=0,$options=['mergeFirstColumn'=>true]){

        if(isset($data[0]) && count($header)!=count($data[0])){
            throw new InvalidParamException('The header number different with data number.');
        }
        // Add some data
        if($this->objPHPExcel->getSheetCount()<=$sheetIndex){
            $this->objPHPExcel->createSheet();
        }
        $this->objPHPExcel->setActiveSheetIndex($sheetIndex);
        $last_item = 0;
        $last_cent = 0;
        foreach($header as $index=>$item){
            $cell = $this->cells[$index].'1';
            $this->setCellValue($cell, $item);
            if(is_array($item) && isset($item['width'])){
                $this->objPHPExcel->getActiveSheet()->getColumnDimension($this->cells[$index])->setWidth($item['width']);
            }else{
                $this->objPHPExcel->getActiveSheet()->getColumnDimension($this->cells[$index])->setWidth(20);
            }
            
            if(is_array($item) && isset($item['col'])){
                $this->objPHPExcel->getActiveSheet()->mergeCells($cell.":".$this->cells[$index+$item['col']].'1');
            }
            
            if(is_array($item) && isset($item['row'])){
                $this->objPHPExcel->getActiveSheet()->mergeCells($cell.":".$this->cells[$index].(1+$item['row']));
            }
        }
        $lastItem = '';
        $lastCell = '';
        foreach($data as $index=>$row){
            $rowNum = $index+2;
            foreach($row as $key=>$item){
                $cell = $this->cells[$key].$rowNum;
                $this->setCellValue($cell, $item);
                
                if(is_array($item) && isset($item['row'])){
                    $this->objPHPExcel->getActiveSheet()->mergeCells($cell.":".$this->cells[$key].($rowNum+$item['row']));
                }
            }
            $this->objPHPExcel->getActiveSheet()->getRowDimension($rowNum)->setRowHeight($rowHeight);
            if($lastItem == $row[0] && trim($lastItem) != '' && $options['mergeFirstColumn']){
                $this->objPHPExcel->getActiveSheet()->mergeCells( $lastCell . ':' .$this->cells[0].$rowNum);
            }
            $lastItem = $row[0];
            $lastCell = $this->cells[0].$rowNum;
        }

        $this->objPHPExcel->getActiveSheet()->setTitle($sheetTitle);
    }

    public function setActiveSheetIndex($index=0){
        $this->objPHPExcel->setActiveSheetIndex($index);
    }

    /**
     * 生成文件到指定目录
     * @param $path
     * @throws \PHPExcel_Reader_Exception
     */
    public function saveFileTo($path,$type='Excel5',$options=[]){
        $objWriter = \PHPExcel_IOFactory::createWriter($this->objPHPExcel,$type);
        if(strtolower($type) == 'csv'){
            if(isset($options['enclosure'])){
            }else{
                $objWriter->setEnclosure('');
            }
        }
        if(!is_dir(dirname ($path))){
            mkdir(dirname ($path),0755,true);
        }
        $objWriter->save($path);
    }

    /**
     * 单元格中设置内容
     * @param $cell
     * @param $item
     */
    private function setCellValue($cell,$item){
         if(is_array($item)){
            if(isset($item['type']) && $item['type']=='image'){
                $this->setCellImage($cell, $item['value']);
            }else{
                $this->objPHPExcel->getActiveSheet()->setCellValue($cell, $item['value']);
            }

        }else{
             $this->objPHPExcel->getActiveSheet()->setCellValue($cell, $item);
         }
    }

    /**
     * 设置图片
     * @param $cell
     * @param $imagePath
     * @throws \PHPExcel_Exception
     */
    private function setCellImage($cell,$imagePath){

        if(empty($imagePath)){
            $imagePath='images/no_image.gif';
        }

        $objDrawing = new \PHPExcel_Worksheet_Drawing();
        $objDrawing->setName('Image');
        $objDrawing->setDescription('');
        $objDrawing->setPath(ltrim($imagePath,'/'));
        $objDrawing->setCoordinates($cell);
        $objDrawing->setHeight(100);
        $objDrawing->setWorksheet($this->objPHPExcel->getActiveSheet());
    }
}