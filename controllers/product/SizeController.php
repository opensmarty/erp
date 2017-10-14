<?php

namespace app\controllers\product;

use app\controllers\BaseController;
use app\models\Category;
use app\models\product\Size as SizeModel;
use Yii;

class SizeController extends BaseController
{
    /**
     * Updates an existing Product model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate()
    {
        $model = new SizeModel();
        $post = Yii::$app->request->post();
        if(!empty($post)){
            $model->deleteAll(['not in','id',$post['id']]);
            foreach($post['size'] as $index=>$size){
                $model = new SizeModel();
                $id = intval($post['id'][$index]);
                if($id>0){
                    $model = $model->findOne(['id'=>$id]);
                }

                $model->size = $size;
                $model->alias = $post['alias'][$index];
                $model->save();
                $this->redirect(['update']);
            }
        }
        $data = $model->find()->all();
        return $this->render('update', [
            'data'=>$data
        ]);
    }
}
