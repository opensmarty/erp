<?php
/**
 * RejectsController.php 
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/7/20
 */

namespace app\controllers\distribution;

use app\helpers\Options;
use app\models\Category;
use app\models\order\Item;
use app\models\order\StockOrderRejected;
use renk\yiipal\components\ExportData;
use renk\yiipal\helpers\FileHelper;
use yii;
use app\controllers\BaseController;

class RejectsController extends BaseController{
    public function actionIndex(){
        $searchModel = new StockOrderRejected();
        $categories = Category::find()->indexBy('id')->asArray()->all();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $models = $dataProvider->getModels();
        foreach($models as &$model){
            $model->categories = $categories;
        }
        $gets = $this->get('StockOrderRejected',[]);
        $tags = isset($gets['reject_tags'])?$gets['reject_tags']:'';
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'tags' => $tags,
        ]);
    }

    public function actionSolved($id){
        if($this->isPost()){
            $posts = Yii::$app->request->post();
            if($posts['number']>=0){
                $model = new StockOrderRejected();
                $model->solved($id, $posts['number']);
                return $this->json_output();
            }else{
                return $this->json_output(['status'=>0,'msg'=>'解决数量必须大于等于0.']);
            }
        }else{
            $model = StockOrderRejected::find()->where(['id'=>$id])->one();
            return $this->renderAjax('//fragment/rejects-solved',['model'=>$model]);
        }
    }

    public function actionMarkRejects($id){
        $post = $this->post();
        $item = Item::findOne($id);
        if(empty($item)){
            return $this->json_output(['status'=>0,'msg'=>'处理失败']);
        }
        if($this->isPost()) {
            $model = new StockOrderRejected();
            $model->markItemAsRejects($item,$post);
            return $this->json_output();
        }else{
            return $this->renderAjax('//fragment/mark-rejects',['model'=>$item]);
        }
    }

    /**
     * 导出次品
     * @param $ids
     * @return array
     */
    public function actionExport(){
        $ids = $this->post('ids');
        if(!empty($ids)) {
            $ids = explode(",", $ids);
            $items = StockOrderRejected::find()->with('product')->with('customOrder')->with('stockOrder')->with('reportUser')->with('solvedUser')
                ->where(['in','id',$ids])
                ->orderBy('created_at DESC')->all();
            $header = $this->createExportHeader();
            $data = $this->formatExportData($items);
            $objExportData = new ExportData($header,$data);
            $objExportData->createExcel($header,$data);
            $path = 'download/rejects/'.date("Y-m-d").'/'.'products-rejects-'.date('Y-m-d-H-i-s').'.xls';
            $objExportData->saveFileTo($path);
            return $this->json_output(['data'=>['/'.$path]]);
        }else{
            return $this->json_output(['status'=>0,'msg'=>'请选择要导出的项目']);
        }
    }

    /**
     * 配货单头部
     * @return array
     */
    private function createExportHeader(){
        $header = [
            '订单编号',
            'SKU',
            '图片',
            '尺码',
            '刻字',
            '次品总数',
            '次品原因',
            '修复总数',
            '状态',
            '报告人',
            '报告时间',
            '解决人',
            '解决时间',
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
        $productType = ['none'=>'','men'=>'(男)','women'=>'(女)'];
        foreach($items as $index=>$item){
            if($item->item_type == 'stockup'){
                $output[$index][] = 'S-'.$item->stockOrder->ext_order_id;
            }else{
                $output[$index][] = $item->customOrder->ext_order_id;
            }

            $output[$index][] = $item->sku;
            $files = $item->product->getFiles();
            $filePath = FileHelper::getThumbnailPath($files[0]->file_path, '300x300');
            $filePath = str_replace(urlencode("#"),"#",$filePath);
            $output[$index][] = ['type'=>'image','value'=>'./'.$filePath];
            $output[$index][] = $item->size_us.$productType[$item->product_type];
            $output[$index][] = '刻字:'.html_entity_decode($item->engravings).$productType[$item->product_type];
            $output[$index][] = $item->qty_rejected;
            $categories = Category::find()->indexBy('id')->asArray()->all();
            $tags = explode(",",$item->reject_tags);
            $reason = '';
            foreach($tags as $tag){
                if(!isset($categories[$tag]))continue;
                $reason .= $categories[$tag]['name'].",";
            }
            $output[$index][] = rtrim($reason,",");
            $output[$index][] = $item->qty_solved;
            $output[$index][] = Options::rejectsStatus($item->item_status);
            $output[$index][] = $item->reportUser->nick_name;
            $output[$index][] = date("Y-m-d H:i:s",$item->created_at);
            if($item->solvedUser){
                $output[$index][] = $item->solvedUser->nick_name;
            }else{
                $output[$index][] = '';
            }

            if($item->solved_at>0){
                $output[$index][] = date("Y-m-d H:i:s",$item->solved_at);
            }else{
                $output[$index][] = '';
            }
        }
        return $output;
    }
}