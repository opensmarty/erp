<?php

namespace app\controllers\factory;

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
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams,null,[['type'=>'factory']]);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * 修改产品版号
     * @param $id
     * @return array
     */
    public function actionEditTemplateNo(){
        $id = $this->post('pk');
        $value = $this->post('value');
        $model = Product::findOne($id);
        if($model){
            $model->template_no = trim($value);
            $model->save();
            return $this->json_output();
        }else{
            return $this->json_output(['status'=>0,'msg'=>'处理失败']);
        }
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
            if(empty($model->price)){
                $model->price = trim($value);
                $model->save();
            }
            return $this->json_output();
        }else{
            return $this->json_output(['status'=>0,'msg'=>'处理失败']);
        }
    }
}
