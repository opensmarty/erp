<?php
use renk\yiipal\helpers\Html;
use yii\helpers\Url;
use renk\yiipal\grid\GridView;
use yii\bootstrap\ActiveForm;
/* @var $this yii\web\View */
//$this->title = '产品列表';
?>

<?php 

$css = <<<CSS
.product_is_clean{
    display:inline-block;
    padding:10px;
}

CSS;

$this->registerCss($css);
?>

<div class="product-index">
    <div class="body-content">
        <div class="row btn-group-top">
            <div class="col-xs-12">
                <div class="btn-group pull-right">
                    <div class="btn-group-top">
                        <?= Html::authLink('新增', ['create'], ['class' => 'btn btn-success']) ?>
                        <?= Html::authSubmitButton('导出', ['export'], ['class' => 'btn btn-default download','form-class'=>'ajax-form download', 'data-action-before'=>'get_ids']) ?>
                    </div>
                </div>
            </div>
            <div class="col-xs-6">
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'columns' => [
                        [
                            'class' => 'yii\grid\CheckboxColumn',
                            'checkboxOptions' => function ($model, $key, $index, $column) {
                                return ['value' => $model->id];
                            }
                        ],
//                        ['class' => 'yii\grid\SerialColumn'],
                        'name',
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
                            'label' => '类型',
                            'attribute' => 'type',
                            'contentOptions'=>['style'=>'max-width: 100px;min-width: 100px;'],
                            'filter'=>function($data){
                                return $data->getTypeOptions();
                            },
                            'value' => function ($data) {
                                return $data::getTypeLabel($data->type);
                            },
                            'format'=>'raw',
                            'enableSorting'=>true,
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
                            'label' => '版号',
                            'attribute' => 'template_no',
                            'value' => function ($data) {
                                return $data->template_no;
                            },
                            'format'=>'raw',
                            'enableSorting'=>false,
                        ],
                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '录入人',
                            'attribute' => 'uid',
                            'filter'=>false,
                            'value' => function ($data) {
                                return $data->recordUser->nick_name;
                            },
                            'format'=>'raw',
                            'enableSorting'=>false,
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '录入时间',
                            'attribute' => 'created_at',
                            'filter'=>false,
                            'value' => function ($data) {
                                return date("Y-m-d H:i:s",$data->created_at);
                            },
                            'format'=>'raw',
                            'enableSorting'=>false,
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '选款人',
                            'attribute' => 'chosen_uid',
                            'filter'=>false,
                            'value' => function ($data) {
                                if($data->chosenUser){
                                    return $data->chosenUser->nick_name;
                                }else{
                                    return '未知';
                                }

                            },
                            'format'=>'raw',
                            'enableSorting'=>false,
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '进货价',
                            'attribute' => 'price',
                            'visible'=>function(){
                                return Yii::$app->user->can('/permission/price');
                            },
                            'contentOptions'=>['style'=>'max-width: 75px;min-width:75px;width:75px;'],
                            'value' => function ($data) {
                                $output = '';
                                $url = '/product/product/edit-price';
                                if(Yii::$app->user->can(Url::to($url))){
                                    $output .='<span class="item"><a href="#" data-name="price" class="edit-price editable-text" data-type="text" data-min="0" data-pk="'.$data->id.'" data-url="'.$url.'" data-title="进货价">'.$data->price.'<i class="glyphicon glyphicon-pencil"></i></a></span>';
                                }else{
                                    $output = '<span class="item">'.$data->price.'</span>';
                                }
                                return $output;
                            },
                            'format' => 'raw',
                        ],
                        [
                            'class' => 'yii\grid\DataColumn',
                            'attribute' => 'is_clean',
                            'contentOptions'=>['style'=>'max-width: 100px;min-width: 100px;'],
                            'filter'=>function($data){
                                return ['0'=>'否','1'=>'是'];
                            },
                            'value' => function ($data) {
                                $output = '';
                                
                                $labelDanger = '';
                                if($data->is_clean == 1){
                                    $labelDanger = 'label-danger';
                                }
                                
                                $url = '/product/product/edit-is-clean';
                                if(Yii::$app->user->can(Url::to($url))){
                                    $output .='<span class="item '.$labelDanger.' product_is_clean"><a href="#" data-name="is_clean" class="edit-is-clean" data-type="select" data-pk="'.$data->id.'" data-url="'.$url.'" data-title="是否清仓">'.($data->is_clean == 1 ? '是':'否').'<i class="glyphicon glyphicon-pencil"></i></a></span>';
                                }else{
                                    $output = '<span class="item '.$labelDanger.' product_is_clean">'.($data->is_clean == 1 ? '是':'否').'</span>';
                                }
                                return $output;
                            },
                            'format'=>'raw',
                            'enableSorting'=>true,
                        ],
                        [
                            'class' => 'renk\yiipal\grid\ActionColumn',
                            'template' => '{add_stock} {update} {delete}',
                            'contentOptions'=>['style'=>'width: 85px;'],
                        ],
                    ],
                ]); ?>
            </div>
        </div>

    </div>
</div>
