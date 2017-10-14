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
                        <?= Html::authSubmitButton('导出', ['export-stocks'], ['class' => 'btn btn-default download','form-class'=>'ajax-form download', 'data-action-before'=>'get_ids']) ?>
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
                        'sku',
                        [
                            'class' => 'yii\grid\DataColumn',
                            'contentOptions'=>['style'=>'max-width: 80px;padding:0px;'],
                            'label' => '图片',
                            'attribute' => 'file',
                            'value' => function ($data) {
                                $image = $data->product->getMasterImage();
                                return \renk\yiipal\helpers\FileHelper::getThumbnailWithLink($image,$data->id);
                            },
                            'format'=>'raw',
                            'enableSorting'=>false,
                        ],
                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '版号',
                            'attribute' => 'template_no',
                            'value' => function ($data) {
                                return $data->product->template_no;
                            },
                            'format'=>'raw',
                            'enableSorting'=>false,
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '销量',
                            'attribute' => 'sales_info',
                            'value' => function ($data) {
                                if(empty($data->salesInfo)){
                                    return '<span class="item">没有销售数据</span>';
                                }
                                $output = '<ul class="list-group" style="overflow-y: auto;max-height: 200px;">';
                                $typeFlag = '';
                                foreach($data->salesInfo as $item){
                                    $type = '';
                                    if($item['size_type']!='none'){
                                        $type = \app\helpers\Options::ringTypes($item['size_type']);
                                    }
                                    if($typeFlag != $type){
                                        $output .= '<li class="list-group-item list-group-item-info">'.$type.'</li>';
                                        $typeFlag = $type;
                                    }

                                    $size = '';
                                    if(!empty($item['size_us'])){
                                        $size = $item['size_us'].': ';
                                    }
                                    $total = intval($item['qty_ordered']);
                                    $output .= '<li class="list-group-item">'.$size.$total.'</li>';
                                }
                                $output.='</ul>';
                                return $output;
                            },
                            'format'=>'raw',
                            'enableSorting'=>false,
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '总销量',
                            'attribute' => 'qty_ordered',
                            'filter'=>['1-10'=>'1-10','10-50'=>'10-50','50-100'=>'50-100','100-1000'=>'100-1000','1000-10000'=>'1000-10000'],
                            'value' => function ($data) {
                                return '<span class="item">'.$data->qty_ordered.'</span>';;
                            },
                            'format'=>'raw',
                            'enableSorting'=>true,
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '库存',
                            'attribute' => 'stock_total',
                            //'filter'=>false,
                            'filter'=>['-2'=>'没有库存','-1'=>'有库存','1-10'=>'1-10','10-50'=>'10-50','50-100'=>'50-100','100-1000'=>'100-1000','1000-10000'=>'1000-10000'],
                            'value' => function ($data) {
                                if(empty($data->stocksInfo)){
                                    return '<span class="item">没有库存信息</span>';
                                }
                                $output = '<ul class="list-group" style="overflow-y: auto;max-height: 200px;">';
                                $typeFlag = '';
                                foreach($data->stocksInfo as $item){
                                    $type = '';
                                    if($item['type']!='none'){
                                        $type = \app\helpers\Options::ringTypes($item['type']);
                                    }
                                    if($typeFlag != $type){
                                        $output .= '<li class="list-group-item list-group-item-info">'.$type.'</li>';
                                        $typeFlag = $type;
                                    }

                                    $size = '';
                                    if(!empty($item['size_code'])){
                                        $size = $item['size_code'].': ';
                                    }
                                    $total = intval($item['actual_total'])+intval($item['virtual_total']);
                                    $output .= '<li class="list-group-item">'.$size.$total.'(实际：'.intval($item['actual_total']).' 在补：'.intval($item['virtual_total']).')'.'</li>';
                                }
                                $output.='</ul>';
                                return $output;
                            },
                            'format'=>'raw',
                            'enableSorting'=>false,
                        ],

                        [
                            'class' => 'renk\yiipal\grid\DataColumn',
                            'label' => '销售时间',
                            'contentOptions'=>['style'=>'max-width: 150px;min-width: 85px;'],
                            'filter'=>function($model){
                                $gets = Yii::$app->request->get('Sales',[]);
                                $created_at = isset($gets['created_at'])?$gets['created_at']:'';
                                $output = '<input type="text" class="form-control form-daterange pull-right" name="Sales[created_at]" value="'.$created_at.'"/>';
                                return $output;
                            },
                            'attribute' => 'created_at',
                            'value' => function ($data) {
                                $gets = Yii::$app->request->get('Sales',[]);
                                $created_at = isset($gets['created_at'])?$gets['created_at']:'';
                                return '<span class="item">'.$created_at.'</span>';
                            },
                            'format' => 'raw',
                        ],
//
//                        [
//                            'class' => 'renk\yiipal\grid\ActionColumn',
//                            'contentOptions'=>['style'=>'width: 85px;'],
//                        ],
                    ],
                ]); ?>
            </div>
        </div>

    </div>
</div>
