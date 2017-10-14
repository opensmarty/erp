<?php

namespace app\controllers\product;

use app\controllers\BaseController;
use app\models\Category;
use app\models\product\Product as ProductModel;
use app\models\product\Product;
use app\models\product\ProductAttributes;
use app\models\product\Size;
use app\models\product\Stock;
use app\models\User;
use renk\yiipal\components\ExportData;
use renk\yiipal\helpers\FileHelper;
use Yii;

class ProductController extends BaseController
{

    /**
     * 产品列表
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new ProductModel();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView($id){
        $product = Product::findOne($id);
        return $this->render('view', [
            'product'=>$product,
        ]);
    }

    /**
     * 添加产品
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new ProductModel();
        $attributesModel = new ProductAttributes();
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $attributesModel->load(Yii::$app->request->post());
            $attributesModel->product_id=$model->getPrimaryKey();
            $attributesModel->save();
            return $this->redirect(['index']);
        }else{
            $categoryModel = new Category();
            $chosenUsers = User::getRoleUsers(['market']);
            //戒指分类
            $productCategories = $categoryModel->getCategoryOptions(2);
            return $this->render('update', [
                'model' => $model,
                'attributesModel' => $attributesModel,
                'productCategories'=>$productCategories,
                'chosenUsers'=>$chosenUsers,
            ]);
        }
    }

    /**
     * 更新产品.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel(ProductModel::className(),$id);
        $attributesModel = $model->productAttributes;
        if(empty($attributesModel)){
            $attributesModel = new ProductAttributes();
        }
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $attributesModel->load(Yii::$app->request->post());
            $attributesModel->product_id=$model->getPrimaryKey();
            $attributesModel->save();
            return $this->redirect(['index']);
        } else {
            $categoryModel = new Category();
            $chosenUsers = User::getRoleUsers(['market']);
            //戒指分类
            $productCategories = $categoryModel->getCategoryOptions(2);
            return $this->render('update', [
                'model' => $model,
                'attributesModel' => $attributesModel,
                'productCategories'=>$productCategories,
                'chosenUsers'=>$chosenUsers,
            ]);
        }
    }

    /**
     * 更新产品属性
     * @return string
     */
    public function actionUpdateAttributes($id){
        $product = Product::findOne($id);
        $attributesModel = $product->productAttributes;
        if(empty($attributesModel)){
            $attributesModel = new ProductAttributes();
        }
        $product->scenario = 'update_attributes';
        $product->attr_uid = $this->getCurrentUid();
        if ($product->load(Yii::$app->request->post()) && $product->save()) {
            $attributesModel->product_cid = $product->cid;
            $attributesModel->product_type = $product->is_couple;
            $attributesModel->load(Yii::$app->request->post());
            $attributesModel->product_id=$product->getPrimaryKey();
            if($attributesModel->save()){
                return $this->redirect(['/factory/product/index']);
            }
        }
        return $this->render('update-attributes', [
            'product'=>$product,
            'attributesModel'=>$attributesModel,
        ]);
    }

    /**
     * 删除产品.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel(ProductModel::className(),$id)->delete();

        return $this->redirect(['index']);
    }


    /**
     * 导出产品
     */
    public function actionExport(){
        $posts = Yii::$app->request->post();
        if(!empty($posts['ids'])){
            $model = new ProductModel();
            $results = $model->getExportDataProducts(explode(",",$posts['ids']));
            $header = $this->createExportHeader();
            $data = $this->formatExportData($results);
            $objExportData = new ExportData($header,$data);
            $objExportData->createExcel($header,$data);
            $path = 'download/product/'.date("Y-m-d").'/'.'products-'.date('Y-m-d-H-i-s').'.xls';
            $objExportData->saveFileTo($path);
            return $this->json_output(['data'=>['/'.$path]]);
        }else{
            return $this->json_output(['status'=>0,'msg'=>'请选择要导出的项目']);
        }
    }

    /**
     * 补库存
     * @param $id
     * @return string
     */
    public function actionAddStocks($id){
        $product = Product::findOne($id);
        $sizeList = Size::find()->All();
        $stockModel = new Stock();
        if($this->post()){
            $stockModel->addStocks($product,$this->post(),$sizeList);
            return $this->json_output();
        }
        $currentStocks = $stockModel->getStocksByProduct($product);
        return $this->renderAjax('add-stocks',[
            'product'=>$product,
            'sizeList'=>$sizeList,
            'currentStocks'=>$currentStocks,
        ]);
    }

    /**
     * 编辑产品库存
     * @param $id
     * @return string
     */
    public function actionEditStocks($id){
        $product = Product::findOne($id);
        $sizeList = Size::find()->All();
        $stockModel = new Stock();
        if($this->post()){
            $stockModel->editStocks($product,$this->post(),$sizeList);
            return $this->json_output();
        }
        $currentStocks = $stockModel->getStocksByProduct($product);
        return $this->renderAjax('edit-stocks',[
            'product'=>$product,
            'sizeList'=>$sizeList,
            'currentStocks'=>$currentStocks,
        ]);
    }


    /**
     * 修改产品价格
     * @param $id
     * @return array
     */
    public function actionEditPrice(){
        $id = $this->post('pk');
        $value = $this->post('value');
        $model = Product::findOne($id);
        if($model){
            $model->price = trim($value);
            $model->save();
            return $this->json_output();
        }else{
            return $this->json_output(['status'=>0,'msg'=>'处理失败']);
        }
    }
    
    /**
     * 修改是否清仓
     * @param $id
     * @return array|string
     */
    public function actionEditIsClean(){
        $id = $this->post('pk');
        $value = $this->post('value');
        $model = Product::findOne($id);
        $model->is_clean = trim($value);
        if($model && $model->save()){
            return $this->json_output();
        } else {
            return $this->json_output(['status'=>0,'msg'=>'处理失败']);
        }
    }

    /**
     * 导入产品价格
     * @return string
     */
    public function actionImportPrice(){
        $productModel = new Product();
        if ($productModel->load(Yii::$app->request->post()) && $productModel->importPrice()) {
            Yii::$app->session->setFlash('success', '操作成功！');
            return $this->redirect(['index']);
        }
        return $this->render('import-price',[
            'model'=>$productModel
        ]);
    }


    /**
     * 导入产品库存
     * @return string
     */
    public function actionImportStocks(){
        $productModel = new Product();
        if ($productModel->load(Yii::$app->request->post()) && $productModel->importStocks()) {
            Yii::$app->session->setFlash('success', '操作成功！');
            return $this->redirect(['/factory/stocks/stocks-list']);
        }
        return $this->render('import-stocks',[
            'model'=>$productModel
        ]);
    }

    /**
     * 导入产品库存累加
     * @return string
     */
    public function actionImportStocksForIncrement(){
        $productModel = new Product();
        if ($productModel->load(Yii::$app->request->post()) && $productModel->importStocks(true)) {
            Yii::$app->session->setFlash('success', '操作成功！');
            return $this->redirect(['/factory/stocks/stocks-list']);
        }
        return $this->render('import-stocks-increment',[
            'model'=>$productModel
        ]);
    }

    /**
     * 导出的头部
     * @return array
     */
    private function createExportHeader(){
        $header = [
            '编号',
            'SKU',
            '版号',
            '图片',
            '录入人',
            '录入时间',
//            '尺码'
        ];
        return $header;
    }

    /**
     * 格式化要导出的数据
     * @param $data
     * @return array
     */
    private function formatExportData($data){
        $output = [];
        foreach($data as $index=>$row){
            $output[$index][] = $index+1;
            $output[$index][] = $row->sku;
            $output[$index][] = $row->template_no;
            $files = $row->getFiles();
            $filePath = FileHelper::getThumbnailPath($files[0]->file_path, '300x300');
            $filePath = str_replace(urlencode("#"),"#",$filePath);
            $output[$index][] = ['type'=>'image','value'=>'./'.$filePath];
            $output[$index][] = $row->recordUser->nick_name;
            $output[$index][] = date("Y-m-d H:i:s",$row->created_at);
        }
        return $output;
    }
}
