<?php
use renk\yiipal\helpers\Html;
use yii\helpers\Url;
use renk\yiipal\grid\GridView;
use yii\bootstrap\ActiveForm;
/* @var $this yii\web\View */
?>
<div class="product-index">
    <div class="body-content">
        <div class="row btn-group-top">
            <div class="col-xs-12">
                <div class="btn-group pull-right">
                    <div class="btn-group-top">
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
                        ['class' => 'yii\grid\SerialColumn'],
                        'sku',
                        [
                            'class' => 'yii\grid\DataColumn',
                            'contentOptions'=>['style'=>'max-width: 100px;min-width: 100px;'],
                            'label' => '图片',
                            'attribute' => 'file',
                            'value' => function ($data) {
                                $image = $data->product->getMasterImage();
                                $img =\renk\yiipal\helpers\FileHelper::getThumbnailWithLink($image,$data->id);
                                return $img;
                            },
                            'format'=>'raw',
                            'enableSorting'=>false,
                        ],
                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '尺码',
                            'attribute' => 'size',
                            'contentOptions'=>['style'=>'max-width: 100px;min-width: 100px;'],
                            'value' => function ($data) {
                                return $data->size;
                            },
                            'format'=>'raw',
                            'enableSorting'=>true,
                        ],
                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '预测时段',
                            'attribute' => 'date_start',
                            'filter'=>false,
                            'value' => function ($data) {
                                return $data->date_start.'-'.$data->date_end;
                            },
                            'format'=>'raw',
                            'enableSorting'=>false,
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '库存(实际/虚拟)',
                            'attribute' => 'stocks',
                            'filter'=>false,
                            'value' => function ($data) {
                                $stocksInfo = $data->stocksInfo;
                                $stocks = isset($stocksInfo[$data->size_type][$data->size])?$stocksInfo[$data->size_type][$data->size]:['total'=>0,'actual_total'=>'0','virtual_total'=>0];
                                return '<span>'.intval($stocks['total']).'('.intval($stocks['actual_total']).'/'.intval($stocks['virtual_total']).')</span>';
                            },
                            'format'=>'raw',
                            'enableSorting'=>false,
                        ],
                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '预测销量',
                            'attribute' => 'forecast_qty',
                            'filter'=>false,
                            'value' => function ($data) {
                                return $data->forecast_qty;
                            },
                            'format'=>'raw',
                            'enableSorting'=>true,
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '预测待补',
                            'attribute' => 'stocksup',
                            'filter'=>false,
                            'value' => function ($data) {
                                return $data->stocksup;
                            },
                            'format'=>'raw',
                        ],

                        [
                            'class' => 'renk\yiipal\grid\DataColumn',
                            'label' => '预测时间',
                            'contentOptions'=>['style'=>'max-width: 150px;min-width: 85px;'],
                            'filter'=>function($model){
                                $gets = Yii::$app->request->get('Forecasting',[]);
                                $date_end = isset($gets['date_end'])?$gets['date_end']:'';
                                $output = '<input type="text" class="form-control form-datesingle pull-right" name="Forecasting[date_end]" value="'.$date_end.'"/>';
                                return $output;
                            },
                            'attribute' => 'date_end',
                            'value' => function ($data) {
                                return '<span class="item">'.$data->date_end.'</span>';
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
