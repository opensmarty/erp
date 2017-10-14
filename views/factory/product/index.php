<?php
use renk\yiipal\helpers\Html;
use yii\helpers\Url;
use renk\yiipal\grid\GridView;
use app\helpers\Options;
use yii\bootstrap\ActiveForm;
/* @var $this yii\web\View */
?>
<div class="product-index">
    <div class="body-content">
        <div class="row">
            <div class="col-xs-12">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'columns' => [
                        'id',
                        'sku',
                        [
                            'class' => 'yii\grid\DataColumn',
                            'contentOptions'=>['style'=>'max-width: 100px;min-width: 100px;'],
                            'label' => '图片',
                            'attribute' => 'file',
                            'value' => function ($data) {
                                $img =\renk\yiipal\helpers\FileHelper::getThumbnailWithLink($data->getMasterImage(),$data->id);
                                return $img;
                            },
                            'format'=>'raw',
                            'enableSorting'=>false,
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '更新时间',
                            'attribute' => 'updated_at',
                            'filter'=>false,
                            'value' => function ($data) {
                                return date("Y-m-d H:i:s",$data->updated_at);
                            },
                            'format'=>'raw',
                            'enableSorting'=>false,
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '产品类型',
                            'attribute' => 'cid',
                            'contentOptions'=>['style'=>'max-width: 100px;min-width: 100px;'],
                            'filter'=>function($data){
                                return ['rings'=>'戒指','ring_single'=>' |--单戒','ring_couple'=>' |--对戒','ring_set'=>' |--套戒','necklace'=>'项链','bracelet'=>'手链','earrings'=>'耳环'];
                            },
                            'value' => function ($data) {
                                $output = '';
                                switch($data->cid){
                                    case 4:
                                        $output = '项链';
                                        break;
                                    case 5:
                                        $output = '手链';
                                        break;
                                    case 25:
                                        $output = '耳环';
                                        break;
                                    case 3:
                                        if($data->is_couple == 1) {
                                            $output = '戒指-对戒';
                                        }else if($data->is_couple == 2){
                                            $output = '戒指-套戒';
                                        }else{
                                            $output = '戒指-单戒';
                                        }
                                        break;
                                }
                                if($data->type == 'virtual'){
                                    $output = '';
                                }
                                return $output;
                            },
                            'format'=>'raw',
                            'enableSorting'=>true,
                        ],
                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '完整性',
                            'attribute' => 'attr_complete',
                            'filter'=>Options::yesNoOptions(),
                            'value' => function ($data) {
                                if(empty($data->attr_uid)){
                                    return '否';
                                }else{
                                    return '是';
                                }
                            },
                            'format'=>'raw',
                            'enableSorting'=>false,
                        ],
                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '编辑人员',
                            'attribute' => 'attr_uid',
                            'value' => function ($data) {
                                if(isset($data->attrUser)){
                                    return $data->attrUser->nick_name;
                                }else{
                                    return '';
                                }
                            },
                            'format'=>'raw',
                            'enableSorting'=>false,
                        ],
                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '版号',
                            'attribute' => 'template_no',
                            'value' => function ($data) {
                                $output = '';
                                $url = '/factory/product/edit-template-no';
                                if(Yii::$app->user->can(Url::to($url))){
                                    $output .='<span class="item"><a href="#" data-name="template_no" class="edit-template_no editable-text" data-type="text" data-pk="'.$data->id.'" data-url="'.$url.'" data-title="版号">'.$data->template_no.'<i class="glyphicon glyphicon-pencil"></i></a></span>';
                                }else{
                                    $output = '<span class="item">'.$data->template_no.'</span>';
                                }
                                return $output;
                            },
                            'format' => 'raw',
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '价格',
                            'attribute' => 'factory_price',
                            'visible'=>function(){
                                return Yii::$app->user->can('/factory/product/edit-price');
                            },
                            'filter'=>['1'=>'已填写','-1'=>'未填写'],
                            'value' => function ($data) {
                                $output = '';
                                $url = '/factory/product/edit-price';
                                if(Yii::$app->user->can(Url::to($url)) && empty($data->price)){
                                    $output .='<span class="item"><a href="#" data-name="price" class="edit-price editable-once-text" data-type="text" data-pk="'.$data->id.'" data-url="'.$url.'" data-title="价格">'.$data->price.'<i class="glyphicon glyphicon-pencil"></i></a></span>';
                                }else{
                                    $output = '<span class="item">'.$data->price.'</span>';
                                }
                                return $output;
                            },
                            'format' => 'raw',
                        ],

                        [
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{update}',
                            'buttons' => [
                                'update' => function($url, $model) {
                                    if(Yii::$app->user->can('/product/product/update-attributes')){
                                        return Html::a('编辑',\renk\yiipal\helpers\Url::to(['/product/product/update-attributes','id'=>$model->id]));
                                    }else{
                                        return '';
                                        }
                                    }
                                ]
                            ],
                    ],
                ]); ?>
            </div>
        </div>

    </div>
</div>
