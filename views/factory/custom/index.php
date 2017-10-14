<?php
use renk\yiipal\helpers\Html;
use yii\helpers\Url;
use renk\yiipal\grid\GridView;
use renk\yiipal\helpers\FileHelper;
use app\helpers\ItemStatus;
/* @var $this yii\web\View */
?>
<div class="order-index">
    <div class="body-content">
        <div class="row btn-group-top">
            <div class="col-xs-12">
                <div class="btn-group pull-right">
                    <div class="btn-group-top">
                        <?= Html::authSubmitButton('导出', ['export-for-custom-produce'], ['class' => 'btn btn-default download','form-class'=>'ajax-form download', 'data-action-before'=>'get_ids']) ?>
                        <?= Html::authSubmitButton('开始生产', ['start'], ['class' => 'btn btn-success','form-class'=>'ajax-form download', 'data-action-before'=>'get_ids']) ?>
                        <?= Html::authSubmitButton('确认加急', ['expedited-confirm'], ['class' => 'btn btn-danger','form-class'=>'ajax-form', 'data-action-before'=>'get_ids']) ?>
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

                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '编号',
                            'attribute' => 'ext_order_id',
                            'value' => function ($data) {
                                return '<span class="item">'.$data->order->ext_order_id.'</span>';;
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
                                $expedited = $data->orderExpedited;
                                if($expedited && $expedited->status == 'confirmed'){
                                    $output = '<media class="label label-success"><i class="fa fa-clock-o"></i>加急</small>';
                                }
                                elseif($data->order->expedited == 1){
                                    $output = '<media class="label label-danger"><i class="fa fa-clock-o"></i>加急</small>';
                                }
                                return '<span class="item">'.$output.'</span>';
                            },
                            'format' => 'raw',
                        ],

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
                            'label' => '产品编号',
                            'attribute' => 'product_id',
                            'value' => function ($data) {
                                $output = '';
                                $output.='<span class="item">'.$data->product_id.'</span>';
                                return $output;
                            },
                            'format' => 'raw',
                        ],
                        
                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => 'SKU',
                            'attribute' => 'sku',
                            'value' => function ($data) {
                                $output = '';
                                $output.='<span class="item">'.$data->sku.'</span>';
                                return $output;
                            },
                            'format' => 'raw',
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '版号',
                            'attribute' => 'template_no',
                            'value' => function ($data) {
                                $output = '';
                                $output.='<span class="item">'.$data->product->template_no.'</span>';
                                return $output;
                            },
                            'format' => 'raw',
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '生产总数',
                            'attribute' => 'qty_ordered',
                            'value' => function ($data) {
                                $output = '';
                                $output.='<span class="item">'.$data->qty_ordered.'</span>';
                                return $output;
                            },
                            'format' => 'raw',
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '待验总数',
                            'attribute' => 'qty_delivery',
                            'value' => function ($data) {
                                $output = '';
                                $output.='<span class="item">'.$data->qty_delivery.'</span>';
                                return $output;
                            },
                            'format' => 'raw',
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '验货通过数',
                            'attribute' => 'qty_passed',
                            'value' => function ($data) {
                                $output = '';
                                $output.='<span class="item">'.$data->qty_passed.'</span>';
                                return $output;
                            },
                            'format' => 'raw',
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '尺码|刻字',
                            'attribute' => 'product_options',
                            'contentOptions'=>['style'=>'min-width: 170px;max-width: 170px;'],
                            'value' => function ($data) {
                                $output = '<span class="item">';
                                if(!empty($data->engravings)){
                                    $output .= '刻字内容：'.$data->engravings.'<br/>';
                                }
                                $output.= '尺码:'.$data->size_original.'<span class="text-danger">[美国码:'.$data->size_us.']</span>';
                                if($data->size_type != 'none'){
                                    $output.='['.\app\helpers\Options::ringTypes($data->size_type).']';
                                }
                                $output .='</span>';
                                return $output;
                            },
                            'format' => 'raw',
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '是否刻字',
                            'filter'=>['1'=>'有','0'=>'无'],
                            'attribute' => 'has_engravings',
                            'value' => function ($data) {
                                $result = '有';
                                if(empty($data->engravings)){
                                    $result = '无';
                                }
                                $output = '';
                                $output.='<span class="item">'.$result.'</span>';
                                return $output;
                            },
                            'format' => 'raw',
                        ],


                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '当前状态',
                            'filter'=>ItemStatus::customStatusOptionsForFactory(),
                            'attribute' => 'item_status',
                            'value' => function ($data) {
                                $output = '';
                                $output.='<span class="item">'.ItemStatus::allStatus($data->item_status).'</span>';
                                return $output;
                            },
                            'format' => 'raw',
                        ],

                        [
                            'class' => 'renk\yiipal\grid\DataColumn',
                            'label' => '开始时间',
                            'contentOptions'=>['style'=>'max-width: 150px;min-width: 85px;'],
                            'filter'=>function($model){
                                $gets = Yii::$app->request->get('Item',[]);
                                $process_at = isset($gets['process_at'])?$gets['process_at']:'';
                                $output = '<input type="text" class="form-control form-daterange pull-right" name="Item[process_at]" value="'.$process_at.'"/>';
                                return $output;
                            },
                            'attribute' => 'process_at',
                            'value' => function ($data) {
                                return '<span class="item">'.date("Y-m-d H:i:s",$data->order->process_at).'</span>';
                            },
                            'format' => 'raw',
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '交货时间',
                            'contentOptions'=>['style'=>'width: 110px;'],
                            'filter'=>false,
                            'attribute' => 'process_at',
                            'value' => function ($data) {
                                $time = 60*60*24*5;
                                return '<span class="item countdown">'.date("Y-m-d H:i:s",$data->order->process_at+$time).'</span>';
                            },
                            'format' => 'raw',
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '超过货期',
                            'filter'=>['0'=>'否','1'=>'是'],
                            'attribute' => 'exceed',
                            'value' => function ($data) {
                                $output = '';
                                if(time()-$data->order->process_at>$data->getDeliveryTime()){
                                    $output.='<span class="item">是</span>';
                                }else{
                                    $output.='<span class="item">否</span>';
                                }

                                return $output;
                            },
                            'format' => 'raw',
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '备注',
                            'contentOptions'=>['style'=>'max-width: 75px;'],
                            'attribute' => 'comment',
                            'value' => function ($data) {
                                $labelDanger = 'label-danger';
                                $output = Html::a('有',['comment/list?target_id='.$data->order_id.'&type=order'],['class'=>'ajax-modal click-green text-white','title'=>'备注']);
                                if(empty($data->comments)){
                                    $output = '无';
                                    $labelDanger = '';
                                }
                                elseif(\app\helpers\CommonHelper::checkHasRead($data->comments)){
                                    $labelDanger = 'label-success';
                                }
                                return '<span class="item '.$labelDanger.'">'.$output.'</span>';
                            },
                            'format' => 'raw',
                        ],

                        [
                            'class' => 'renk\yiipal\grid\ActionColumn',
                            'contentOptions'=>['style'=>'min-width: 75px;'],
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>
</div>
