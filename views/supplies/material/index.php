<?php
use renk\yiipal\helpers\Html;
use yii\helpers\Url;
use renk\yiipal\grid\GridView;
use yii\bootstrap\ActiveForm;
/* @var $this yii\web\View */
//$this->title = '产品列表';
?>
<div class="product-index">
    <div class="body-content">
        <div class="row btn-group-top">
            <div class="col-xs-12">
                <div class="btn-group pull-right">
                    <div class="btn-group-top">
                        <?= Html::authLink('新增', ['create'], ['class' => 'btn btn-success']) ?>
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
//                    'filterModel' => $searchModel,
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        'name',
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
                            'label' => '价格',
                            'attribute' => 'price',
                            'contentOptions'=>['style'=>'max-width: 75px;min-width:75px;width:75px;'],
                            'value' => function ($data) {
                                $output = '<span class="item">'.$data->price.'</span>';
                                return $output;
                            },
                            'format' => 'raw',
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '在补库存',
                            'attribute' => 'foo',
                            'contentOptions'=>['style'=>'max-width: 75px;min-width:75px;width:75px;'],
                            'value' => function ($data) {
                                $packings = $data->packing;
                                if(empty($packings)){
                                    return '<span class="item">0</span>';
                                }
                                $output = '';
                                foreach($packings as $packing){
                                    $output .= '<span class="item">'.($packing->qty-$packing->qty_delivered).'</span>';
                                }
                                return $output;
                            },
                            'format' => 'raw',
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '库存(实际/在补)',
                            'attribute' => 'quantity',
                            'contentOptions'=>['style'=>'max-width: 75px;min-width:75px;width:75px;'],
                            'value' => function ($data) {
                                $virtualStocks = 0;
                                $packings = $data->packing;
                                if(empty($packings)){
                                    $virtualStocks = 0;
                                }else{
                                    foreach($packings as $packing){
                                        $virtualStocks += ($packing->qty-$packing->qty_delivered);
                                    }
                                }

                                $output = '<span class="item">'.$data->quantity.'/'.$virtualStocks.'</span>';
                                return $output;
                            },
                            'format' => 'raw',
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
