<?php
use renk\yiipal\helpers\Html;
use yii\helpers\Url;
use renk\yiipal\grid\GridView;
use renk\yiipal\helpers\FileHelper;
use app\helpers\ItemStatus;
use app\helpers\CommonHelper;
/* @var $this yii\web\View */
?>
<div class="order-index">
    <div class="body-content">
        <div class="row btn-group-top">
            <div class="col-xs-12">
                <div class="btn-group pull-right">
                    <div class="btn-group-top">
                        <?= Html::authSubmitButton('导出', ['export'], ['class' => 'btn btn-default download','form-class'=>'ajax-form download', 'data-action-before'=>'get_ids']) ?>
                        <?= Html::authSubmitButton('发货', ['/distribution/custom/ship'], ['class' => 'btn btn-danger confirm download','form-class'=>'ajax-form download', 'data-action-before'=>'get_ids']) ?>
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
                                $extraInfo = '';
                                if($data->order->changed>0){
                                    $extraInfo = '(换)';
                                }
                                return '<span class="item">'.$data->order->ext_order_id.$extraInfo.'</span>';
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
                                if($data->order->expedited && $data->order->expedited == 1){
                                    $output = '<small class="label label-danger"><i class="fa fa-clock-o"></i>加急</small>';
                                }
                                return '<span class="item">'.$output.'</span>';
                            },
                            'format' => 'raw',
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '订单号',
                            'attribute' => 'increment_id',
                            'value' => function ($data) {
                                return '<span class="item">'.$data->order->increment_id.'</span>';;
                            },
                            'format' => 'raw',
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '物流公司',
                            'attribute' => 'shipping_method',
                            'contentOptions'=>['style'=>'max-width: 45px;min-width:45px;'],
                            'filter'=>\app\helpers\Options::shippingMethods(),
                            'value' => function ($data) {
                                $output = '';
                                $url = '/order/order/edit-shipping-info';
                                if(CommonHelper::canEditOrder($data->order) && Yii::$app->user->can(Url::to($url))){
                                    $output .='<span class="item"><a href="#" data-name="shipping_method" class="edit-shipping-method" data-type="select" data-pk="'.$data->order->id.'" data-url="/order/order/edit-shipping-info" data-title="物流公司">'.$data->order->shipping_method.'<i class="glyphicon glyphicon-pencil"></i></a></span>';
                                }else{
                                    $output = '<span class="item">'.$data->order->shipping_method.'</span>';
                                }
                                return $output;
                            },
                            'format' => 'raw',
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '物流单号',
                            'attribute' => 'shipping_track_no',
                            'value' => function ($data) {
                                $output = '';
                                $url = '/order/order/edit-shipping-info';
                                if(CommonHelper::canEditOrder($data->order) && Yii::$app->user->can(Url::to($url))){
                                    $output .='<span class="item"><a href="#" data-name="shipping_track_no" class="editable-text" data-type="text" data-pk="'.$data->order->id.'" data-url="/order/order/edit-shipping-info" data-title="物流单号">'.$data->order->shipping_track_no.'<i class="glyphicon glyphicon-pencil"></i></a></span>';
                                }else{
                                    $output = '<span class="item">'.$data->order->shipping_track_no.'</span>';
                                }
                                return $output;
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
                            'label' => '备注',
                            'contentOptions'=>['style'=>'max-width: 75px;'],
                            'attribute' => 'comment',
                            'value' => function ($data) {
                                $output = Html::a('有',['comment/list?target_id='.$data->order_id.'&type=order'],['class'=>'ajax-modal click-green text-white','title'=>'备注']);
                                $labelDanger = 'label-danger';
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
                            'label' => '配货数量',
                            'attribute' => 'qty_ordered',
                            'value' => function ($data) {
                                $labelDanger = '';
                                if($data->qty_ordered>1){
                                    $labelDanger = 'label-danger';
                                }
                                $output = '';
                                $output.='<span class="item '.$labelDanger.'">'.$data->qty_ordered.'</span>';
                                return $output;
                            },
                            'format' => 'raw',
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '库存数量',
                            'attribute' => 'qty_delivery',
                            'value' => function ($data) {
                                $output = '';
                                $stockNumber = $data->stocks?$data->stocks->total:0;
                                $output.='<span class="item">'.$stockNumber.'</span>';
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
                                if(!empty($data->size_us)){
                                    $output.= '网站尺码:'.$data->size_original.'<br/>';
                                    $output.= '实际尺码:'.$data->size_us;
                                }
                                if($data->size_type != 'none'){
                                    $output.='['.\app\helpers\Options::ringTypes($data->size_type).']';
                                }

                                $output.= '</span>';
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
                            'filter'=>ItemStatus::statusOptionForDistribution(),
                            'attribute' => 'item_status',
                            'value' => function ($data) {
                                if($data->product->type == 'taobao' && !empty($data->product->taobao_url)){
                                    $output ='<span class="item"><a href="'.$data->product->taobao_url.'" target="_blank">'.ItemStatus::allStatus($data->item_status).'</a></span>';
                                }else{
                                    $output ='<span class="item">'.ItemStatus::allStatus($data->item_status).'</span>';
                                }

                                return $output;
                            },
                            'format' => 'raw',
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '变更记录',
                            'filter'=>ItemStatus::trackStatus(),
                            'attribute' => 'last_track_status',
                            'value' => function ($data) {
                                $fontColor = 'text-danger';
                                $output = Html::a(ItemStatus::TrackStatus($data->order->last_track_status),['comment/list?target_id='.$data->order->id.'&type=order&group=confirm'],['class'=>'ajax-modal text-danger','title'=>'备注']);
                                if($data->order->last_track_status == \app\models\order\Order::TASK_STATUS_NORMAL){
                                    $fontColor = '';
                                    $output = ItemStatus::TrackStatus($data->order->last_track_status);
                                }
                                if($data->order->last_track_status == \app\models\order\Order::TASK_STATUS_CHANGE_CONFIRMED){
                                    $fontColor = 'text-success';
                                }
                                return '<span class="item '.$fontColor.'">'.$output.'</span>';
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
                            'label' => '待定',
                            'contentOptions'=>['style'=>'max-width: 45px;min-width: 45px;'],
                            'filter'=>['1'=>'产品','2'=>'地址','0'=>'否'],
                            'attribute' => 'paused',
                            'value' => function ($data) {
                                $label = '否';
                                $class = '';
                                if($data->order->paused==1){
                                    $label = '产品';
                                    $class = 'text-red';
                                }
                                if($data->order->paused==2){
                                    $label = '地址';
                                    $class = 'text-red';
                                }
                                return '<span class="item '.$class.'">'.$label.'</span>';
                            },
                            'format' => 'raw',
                        ],

                        [
                            'class' => 'renk\yiipal\grid\ActionColumn',
                            'contentOptions'=>['style'=>'max-width: 75px;min-width: 65px;'],
                        ],
                    ],
                ]); ?>
            </div>
        </div>

    </div>
</div>

<div class="modal fade" id="accept-request-modal" tabindex="-1" role="dialog" aria-labelledby="accept-request-label">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <?= $form = Html::beginForm(Url::to(['request-accept']),'post',['class'=>'ajax-form']);?>
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="exampleModalLabel">验收通过</h4>
            </div>
            <div class="modal-body">
                    <div class="form-group">
                        <label for="request-accept-number" class="control-label">验收数量:</label>
                        <input type="number" name="number" step=1 min="1" class="form-control" id="request-accept-number" value="1">
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                <button type="submit" class="btn btn-primary">提交</button>
            </div>
            <?= Html::endForm();?>
        </div>
    </div>
</div>
