<?php
use renk\yiipal\helpers\Html;
use yii\helpers\Url;
use renk\yiipal\grid\GridView;
use app\helpers\Options;
use yii\bootstrap\ActiveForm;
/* @var $this yii\web\View */
?>
<div class="product-template-index">
    <div class="body-content">
        <div class="row btn-group-top">
            <div class="col-xs-12">
                <div class="btn-group pull-right">
                    <div class="btn-group-top">
                        <?= Html::authSubmitButton('确认支付', ['paid'], ['class' => 'btn btn-success','form-class'=>'ajax-form', 'data-action-before'=>'get_ids']) ?>
                    </div>
                </div>
            </div>
            <div class="col-xs-6">
            </div>
        </div>
        <div class="row">
            <div class="col-lg-3 col-xs-6">
                <!-- small box -->
                <div class="small-box bg-aqua">
                    <div class="inner">
                        <h3><?=$staticInfo['total_number'];?></h3>

                        <p>累计渲染数目</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-bag"></i>
                    </div>
                </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-xs-6">
                <!-- small box -->
                <div class="small-box bg-aqua">
                    <div class="inner">
                        <h3><?=$staticInfo['total_price'];?></h3>

                        <p>渲染总费用</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-stats-bars"></i>
                    </div>
                </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-xs-6">
                <!-- small box -->
                <div class="small-box bg-green">
                    <div class="inner">
                        <h3><?=$staticInfo['paid'];?></h3>

                        <p>已支付</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-stats-bars"></i>
                    </div>
                </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-xs-6">
                <!-- small box -->
                <div class="small-box bg-yellow">
                    <div class="inner">
                        <h3><?=$staticInfo['total_price']-$staticInfo['paid'];?></h3>

                        <p>未支付</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-stats-bars"></i>
                    </div>
                </div>
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
                        'ext_id',
                        'sku',
                        'template_no',
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
                            'contentOptions'=>['style'=>'max-width: 65px;'],
                            'filter'=>function($data){
                                return ['1'=>'变体','0'=>'新版'];
                            },
                            'attribute' => 'type',
                            'value' => function ($data) {
                                $output= '新版';
                                if($data->type ==1){
                                    $output = '变体';
                                }
                                return '<span class="item">'.$output.'</span>';
                            },
                            'format' => 'raw',
                        ],

                        [
                            'class' => 'renk\yiipal\grid\DataColumn',
                            'label' => '结束时间',
                            'contentOptions'=>['style'=>'max-width: 165px;min-width: 165px;width:165px;'],
                            'attribute' => 'finished_at',
                            'filter'=>function($model){
                                $gets = Yii::$app->request->get('ProductTemplate',[]);
                                $date = isset($gets['finished_at'])?$gets['finished_at']:'';
                                $output = '<input type="text" class="form-control form-daterange pull-right" name="ProductTemplate[finished_at]" value="'.$date.'"/>';
                                return $output;
                            },
                            'value' => function ($data) {
                                return $data->finished_at?date("Y-m-d H:i:s",$data->finished_at):'未结束';
                            },
                            'format'=>'raw',
                            'enableSorting'=>false,
                        ],
                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '渲染类型',
                            'attribute' => 'render_type',
                            'value' => function ($data) {
                                $output = '';
                                $url = '/product/template/edit-render-type';
                                if(Yii::$app->user->can(Url::to($url))){
                                    $output .='<span class="item"><a href="#" data-name="render_type" class="edit-render_type editable-text" data-type="text" data-pk="'.$data->id.'" data-url="'.$url.'" data-title="渲染类型">'.$data->render_type.'<i class="glyphicon glyphicon-pencil"></i></a></span>';
                                }else{
                                    $output = '<span class="item">'.$data->render_type.'</span>';
                                }
                                return $output;
                            },
                            'format' => 'raw',
                        ],
                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '价格',
                            'attribute' => 'render_price',
                            'filter'=>['1'=>'已填写','-1'=>'未填写'],
                            'visible'=>function(){
                                return Yii::$app->user->can('/product/template/edit-once-price');
                            },
                            'value' => function ($data) {
                                $output = '';
                                $url = '/product/template/edit-once-price';
                                if(Yii::$app->user->can(Url::to($url)) && empty($data->render_price)){
                                    $output .='<span class="item"><a href="#" data-name="render_price" class="edit-price editable-once-text" data-type="text" data-pk="'.$data->id.'" data-url="'.$url.'" data-title="价格">'.$data->render_price.'<i class="glyphicon glyphicon-pencil"></i></a></span>';
                                }else{
                                    $output = '<span class="item">'.$data->render_price.'</span>';
                                }
                                return $output;
                            },
                            'format' => 'raw',
                        ],
                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '价格',
                            'attribute' => 'render_price',
                            'filter'=>['1'=>'已填写','-1'=>'未填写'],
                            'visible'=>function(){
                                return Yii::$app->user->can('/product/template/edit-price');
                            },
                            'value' => function ($data) {
                                $output = '';
                                $url = '/product/template/edit-price';
                                $output .='<span class="item"><a href="#" data-name="render_price" class="edit-price editable-text" data-type="text" data-pk="'.$data->id.'" data-url="'.$url.'" data-title="价格">'.$data->render_price.'<i class="glyphicon glyphicon-pencil"></i></a></span>';
                                return $output;
                            },
                            'format' => 'raw',
                        ],
                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '支付状态',
                            'attribute' => 'payment_status',
                            'filter'=>['paid'=>'已支付','pending'=>'未支付'],
                            'value' => function ($data) {
                                $paymentStatus = ['paid'=>'已支付','pending'=>'未支付'];
                                return isset($paymentStatus[$data->payment_status])?$paymentStatus[$data->payment_status]:null;
                            },
                            'format' => 'raw',
                        ],
//                        [
//                            'class' => 'renk\yiipal\grid\ActionColumn',
//                            'template' => '{add_stock} {update} {delete}',
//                            'contentOptions'=>['style'=>'width: 95px;'],
//                        ],
                    ],
                ]); ?>
            </div>
        </div>

    </div>
</div>
