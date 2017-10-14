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
                        <?= Html::authLink('新增', ['create'], ['class' => 'btn btn-success']) ?>
                        <?= Html::authSubmitButton('开版', ['factory-start'], ['class' => 'btn btn-primary confirm','form-class'=>'ajax-form', 'data-action-before'=>'get_ids']) ?>
                        <?= Html::authSubmitButton('开始渲染', ['studio-start'], ['class' => 'btn btn-primary confirm','form-class'=>'ajax-form', 'data-action-before'=>'get_ids']) ?>
                        <?= Html::authSubmitButton('确认发起', ['approval'], ['class' => 'btn btn-primary confirm','form-class'=>'ajax-form', 'data-action-before'=>'get_ids']) ?>
                        <?= Html::authSubmitButton('导出', ['export'], ['class' => 'btn btn-default download','form-class'=>'ajax-form download', 'data-action-before'=>'get_ids']) ?>
                        <?= Html::authSubmitButton('确认加急', ['expedited-confirm2'], ['class' => 'btn btn-danger','form-class'=>'ajax-form', 'data-action-before'=>'get_ids']) ?>
                        <?= Html::authSubmitButton('结束流程', ['finished-batch'], ['class' => 'btn btn-danger','form-class'=>'ajax-form', 'data-action-before'=>'get_ids']) ?>
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
                            'label' => '国家',
                            'contentOptions'=>['style'=>'max-width: 75px;width:75px;'],
                            'attribute' => 'country',
                            'filter'=>Options::websiteOptions(),
                            'value' => function ($data) {
                                $output= strtoupper($data->country);
                                return '<span class="item">'.$output.'</span>';
                            },
                            'format' => 'raw',
                        ],
                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '状态',
                            'contentOptions'=>['style'=>'max-width: 65px;'],
                            'attribute' => 'status',
                            'filter'=>Options::templateOptions(),
                            'value' => function ($data) {
                                $label = Options::templateOptions($data->status);
                                $fontColor = '';
                                if($data->status == 'cancelled'){
                                    $fontColor = 'text-danger';
                                }
                                $output = Html::a($label,'/product/template/process-timeline?id='.$data->id,['class'=>'ajax-modal','title'=>'处理历程']);
                                return '<span class="item '.$fontColor.'">'.$output.'</span>';
                            },
                            'format' => 'raw',
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
                            'class' => 'yii\grid\DataColumn',
                            'label' => '版型分类',
                            'contentOptions'=>['style'=>'max-width: 65px;'],
                            'attribute' => 'category',
                            'filter'=>Options::templateCategory(),
                            'value' => function ($data) {
                                $label = Options::templateCategory($data->category);
                                return '<span class="item">'.$label.'</span>';
                            },
                            'format' => 'raw',
                        ],
                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '原创',
                            'contentOptions'=>['style'=>'max-width: 65px;'],
                            'filter'=>function($data){
                                return ['1'=>'原创','0'=>'普通'];
                            },
                            'attribute' => 'is_original',
                            'value' => function ($data) {
                                $output= '普通';
                                if($data->is_original ==1){
                                    $output = '原创';
                                }
                                return '<span class="item">'.$output.'</span>';
                            },
                            'format' => 'raw',
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '电镀颜色',
                            'contentOptions'=>['style'=>'max-width: 65px;'],
                            'attribute' => 'electroplating_color',
                            'filter'=>Options::electroplatingColor(false,''),
                            'value' => function ($data) {
                                $label = Options::electroplatingColor($data->electroplating_color);
                                return '<span class="item">'.$label.'</span>';
                            },
                            'format' => 'raw',
                        ],
                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '加急',
                            'contentOptions'=>['style'=>'max-width: 65px;'],
                            'filter'=>function($data){
                                return ['1'=>'是','0'=>'否'];
                            },
                            'attribute' => 'expedited',
                            'value' => function ($data) {
                                $output= '否';
                                if($data->expedited ==2){
                                    $output = '<media class="label label-success"><i class="fa fa-clock-o"></i>加急</small>';
                                }
                                elseif($data->expedited == 1){
                                    $output = '<small class="label label-danger"><i class="fa fa-clock-o"></i>加急</small>';
                                }
                                return '<span class="item">'.$output.'</span>';
                            },
                            'format' => 'raw',
                        ],
                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '备注',
                            'contentOptions'=>['style'=>'max-width: 75px;'],
                            'attribute' => 'comment',
                            'value' => function ($data) {
                                $output = Html::a('有',['comment/list?target_id='.$data->id.'&type=template'],['class'=>'ajax-modal text-white','title'=>'备注']);
                                $labelDanger = 'label-danger';
                                if(empty($data->comments)){
                                    $output = '无';
                                    $labelDanger = '';
                                }
                                return '<span class="item '.$labelDanger.'">'.$output.'</span>';
                            },
                            'format' => 'raw',
                        ],
                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '选品人',
                            'attribute' => 'chosen_uid',
                            'filter'=>true,
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
                            'label' => '发起人',
                            'attribute' => 'create_uid',
                            'filter'=>false,
                            'value' => function ($data) {
                                return $data->createUser->nick_name;
                            },
                            'format'=>'raw',
                            'enableSorting'=>false,
                        ],
                        [
                            'class' => 'renk\yiipal\grid\DataColumn',
                            'label' => '发起时间',
                            'attribute' => 'created_at',
                            'contentOptions'=>['style'=>'max-width: 95px;min-width: 95px;width:95px;'],
                            'filter'=>function($model){
                                $gets = Yii::$app->request->get('ProductTemplate',[]);
                                $created_at = isset($gets['created_at'])?$gets['created_at']:'';
                                $output = '<input type="text" class="form-control form-daterange pull-right" name="ProductTemplate[created_at]" value="'.$created_at.'"/>';
                                return $output;
                            },
                            'value' => function ($data) {
                                return date("Y-m-d H:i:s",$data->created_at);
                            },
                            'format'=>'raw',
                            'enableSorting'=>false,
                        ],
                        [
                            'class' => 'renk\yiipal\grid\DataColumn',
                            'label' => '审核时间',
                            'contentOptions'=>['style'=>'max-width: 95px;min-width: 95px;width:95px;'],
                            'attribute' => 'approval_at',
                            'filter'=>function($model){
                                $gets = Yii::$app->request->get('ProductTemplate',[]);
                                $date = isset($gets['approval_at'])?$gets['approval_at']:'';
                                $output = '<input type="text" class="form-control form-daterange pull-right" name="ProductTemplate[approval_at]" value="'.$date.'"/>';
                                return $output;
                            },
                            'value' => function ($data) {
                                return $data->approval_at?date("Y-m-d H:i:s",$data->approval_at):'未审核';
                            },
                            'format'=>'raw',
                            'enableSorting'=>false,
                        ],
                        [
                            'class' => 'renk\yiipal\grid\DataColumn',
                            'label' => '流程结束时间',
                            'contentOptions'=>['style'=>'max-width: 95px;min-width: 95px;width:95px;'],
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
                            'class' => 'renk\yiipal\grid\ActionColumn',
                            'template' => '{add_stock} {update} {delete}',
                            'contentOptions'=>['style'=>'width: 95px;'],
                        ],
                    ],
                ]); ?>
            </div>
        </div>

    </div>
</div>
