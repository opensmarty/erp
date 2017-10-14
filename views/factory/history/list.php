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

                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '编号',
                            'attribute' => 'ext_order_id',
                            'value' => function ($data) {
                                if($data->order_type == 'custom'){
                                    $ext_order_id = $data->customOrder->ext_order_id;
                                }else{
                                    $ext_order_id = 'S-'.$data->stockOrder->ext_order_id;
                                }
                                return '<span class="item">'.$ext_order_id.'</span>';;
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
                                $expedited = $data->expedited;
                                if($expedited){
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
                            'label' => '生产总数',
                            'attribute' => 'qty_ordered',
                            'value' => function ($data) {
                                $output = '';
                                $output.='<span class="item">'.$data->qty_passed_total.'/'.$data->qty_ordered.'</span>';
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
                            'label' => '单价',
                            'attribute' => 'price',
                            'visible'=>function(){
                                return Yii::$app->user->can('/permission/price');
                            },
                            'value' => function ($data) {
                                return '<span class="item">￥'.$data->price.'</span>';
                            },
                            'format' => 'raw',
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '总价',
                            'attribute' => 'total_price',
                            'visible'=>function(){
                                return Yii::$app->user->can('/permission/price');
                            },
                            'value' => function ($data) {
                                return '<span class="item">￥'.round($data->price*$data->qty_passed,2).'</span>';
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
                                $output.= '美国码：'.$data->size_us;
                                if($data->product_type != 'none'){
                                    $output.='['.\app\helpers\Options::ringTypes($data->product_type).']';
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
                            'label' => '订单类型',
                            'contentOptions'=>['style'=>'max-width: 75px;'],
                            'filter'=>['custom'=>'定制单','stock'=>'库存单'],
                            'attribute' => 'order_type',
                            'value' => function ($data) {
                                return '<span class="item">'.ItemStatus::orderTypeOptions($data->order_type).'</span>';
                            },
                            'format' => 'raw',
                        ],

                        [
                            'class' => 'renk\yiipal\grid\DataColumn',
                            'label' => '开始时间',
                            'contentOptions'=>['style'=>'max-width: 150px;min-width: 85px;'],
                            'filter'=>function($model){
                                $gets = Yii::$app->request->get('ProductDelivered',[]);
                                $start_at = isset($gets['start_at'])?$gets['start_at']:'';
                                $output = '<input type="text" class="form-control form-daterange pull-right" name="ProductDelivered[start_at]" value="'.$start_at.'"/>';
                                return $output;
                            },
                            'attribute' => 'start_at',
                            'value' => function ($data) {
                                return '<span class="item">'.date("Y-m-d H:i:s",$data->start_at).'</span>';
                            },
                            'format' => 'raw',
                        ],

                        [
                            'class' => 'renk\yiipal\grid\DataColumn',
                            'label' => '交货时间',
                            'contentOptions'=>['style'=>'width: 110px;'],
                            'filter'=>function($model){
                                $gets = Yii::$app->request->get('ProductDelivered',[]);
                                $created_at = isset($gets['created_at'])?$gets['created_at']:'';
                                $output = '<input type="text" class="form-control form-daterange pull-right" name="ProductDelivered[created_at]" value="'.$created_at.'"/>';
                                return $output;
                            },
                            'attribute' => 'created_at',
                            'value' => function ($data) {
                                return '<span class="item">'.date("Y-m-d H:i:s",$data->created_at).'</span>';
                            },
                            'format' => 'raw',
                        ],

                        [
                            'class' => 'renk\yiipal\grid\DataColumn',
                            'label' => '生产时间',
                            'contentOptions'=>['style'=>'width: 130px;'],
                            'filter'=>function($model){
                                $options = [0=>''];
                                for($i=1;$i<=30;$i++){
                                    $day = 60*60*24;
                                    $options[$i*$day] = '大于'.$i.'天';
                                }
                                $gets = Yii::$app->request->get('ProductDelivered',[]);
                                $duration_time = isset($gets['duration_time'])?$gets['duration_time']:'';
                                $output = Html::dropDownList('ProductDelivered[duration_time]',$duration_time,$options,['class'=>'form-control']);
                                return $output;
                            },
                            'attribute' => 'duration_time',
                            'value' => function ($data) {
                                $time = intval($data->duration_time);
                                $day = floor($time / 86400);
                                $hour = floor(($time - $day * 86400) / 3600);
                                $minute = floor(($time - ($day * 86400) - ($hour * 3600) ) / 60);
                                $second = floor($time - ($day * 86400) - ($hour * 3600) - ($minute * 60));

                                $output =  sprintf('%d天%d时%d分%d秒', $day, $hour, $minute, $second);
                                return '<span class="item">'.$output.'</span>';
                            },
                            'format' => 'raw',
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>
</div>
