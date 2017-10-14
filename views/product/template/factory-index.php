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
                        <?= Html::authSubmitButton('导出', ['export'], ['class' => 'btn btn-default download','form-class'=>'ajax-form download', 'data-action-before'=>'get_ids']) ?>
                        <?= Html::authSubmitButton('确认加急', ['expedited-confirm2'], ['class' => 'btn btn-danger','form-class'=>'ajax-form', 'data-action-before'=>'get_ids']) ?>
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
                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '版号',
                            'attribute' => 'template_no',
                            'value' => function ($data) {
                                $output = '';
                                $url = '/product/template/edit-template-no';
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
                            'class' => 'renk\yiipal\grid\DataColumn',
                            'label' => '开版时间',
                            'contentOptions'=>['style'=>'max-width: 95px;min-width: 95px;width:95px;'],
                            'attribute' => 'factory_start_at',
                            'filter'=>function($model){
                                $gets = Yii::$app->request->get('ProductTemplate',[]);
                                $date = isset($gets['factory_start_at'])?$gets['factory_start_at']:'';
                                $output = '<input type="text" class="form-control form-daterange pull-right" name="ProductTemplate[factory_start_at]" value="'.$date.'"/>';
                                return $output;
                            },
                            'value' => function ($data) {
                                return $data->factory_start_at?date("Y-m-d H:i:s",$data->factory_start_at):'未开版';
                            },
                            'format'=>'raw',
                            'enableSorting'=>false,
                        ],


                        [
                            'class' => 'renk\yiipal\grid\DataColumn',
                            'label' => '开版完成时间',
                            'contentOptions'=>['style'=>'max-width: 95px;min-width: 95px;width:95px;'],
                            'attribute' => 'factory_end_at',
                            'filter'=>function($model){
                                $gets = Yii::$app->request->get('ProductTemplate',[]);
                                $date = isset($gets['factory_end_at'])?$gets['factory_end_at']:'';
                                $output = '<input type="text" class="form-control form-daterange pull-right" name="ProductTemplate[factory_end_at]" value="'.$date.'"/>';
                                return $output;
                            },
                            'value' => function ($data) {
                                return $data->factory_end_at?date("Y-m-d H:i:s",$data->factory_end_at):'未完成';
                            },
                            'format'=>'raw',
                            'enableSorting'=>false,
                        ],

                        [
                            'class' => 'renk\yiipal\grid\DataColumn',
                            'label' => '工厂验收时间',
                            'contentOptions'=>['style'=>'max-width: 95px;min-width: 95px;width:95px;'],
                            'attribute' => 'factory_accepted_at',
                            'filter'=>function($model){
                                $gets = Yii::$app->request->get('ProductTemplate',[]);
                                $date = isset($gets['factory_accepted_at'])?$gets['factory_accepted_at']:'';
                                $output = '<input type="text" class="form-control form-daterange pull-right" name="ProductTemplate[factory_accepted_at]" value="'.$date.'"/>';
                                return $output;
                            },
                            'value' => function ($data) {
                                return $data->factory_accepted_at?date("Y-m-d H:i:s",$data->factory_accepted_at):'未验收';
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
