<?php
use renk\yiipal\helpers\Html;
use yii\helpers\Url;
use renk\yiipal\grid\GridView;
use renk\yiipal\helpers\FileHelper;
use yii\bootstrap\ActiveForm;
use app\helpers\ItemStatus;
/* @var $this yii\web\View */

?>
<?php
    $filterCols = [
        'col-shipped_at'=>'发货时间',
        'col-order_type'=>'订单类型',
        'col-source'=>'来源',
        'col-from'=>'客户端',
        'col-has_shipment'=>'运单',
        'col-item_status'=>'产品状态',
        ];
    if(Yii::$app->user->can('/permission/order-service-column')){
        $extFilterCols =[
            'col-coupon_code'=>'优惠券',
            'col-customer_name'=>'客户姓名',
            'col-customer_email'=>'客户邮件',
        ];
        $filterCols = array_merge($filterCols,$extFilterCols);
    }

    $filterColsList = json_encode(array_keys($filterCols));

?>
<div class="order-index">
    <div class="body-content">
        <div class="row btn-group-top">
            <div class="col-xs-12">
                <div class="btn-group pull-right">
                    <div class="btn-group-top">
                        <?= Html::authSubmitButton('导出', ['export'], ['class' => 'btn btn-default download','form-class'=>'ajax-form download', 'data-action-before'=>'get_ids']) ?>
                        <?= Html::authSubmitButton('导出UPS电子报关单', ['/shipment/shipment/export-ups-eds'], ['class' => 'btn btn-default ','form-class'=>'ajax-form download', 'data-action-before'=>'get_ids']) ?>
                    </div>
                </div>
            </div>
            <div class="col-xs-6">
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <div class="btn-group pull-right">
                    <div class="filter-cols">
                        <?= Html::checkboxList('',null,$filterCols,['class'=>'filter-cols-checkbox'])?>
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
                            },
                            'contentOptions'=>['class'=>'checkbox-column'],
                        ],
                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '编号',
                            'attribute' => 'ext_order_id',
                            'value' => function ($data) {
                                $extraInfo = '';
                                if($data->changed>0){
                                    $extraInfo = '(换)';
                                }
                                return '<span class="item">'.$data->ext_order_id.$extraInfo.'</span>';
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
                                if($data->expedited && $expedited && $expedited->status == 'confirmed'){
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
                            'label' => '订单号',
                            'attribute' => 'increment_id',
                            'value' => function ($data) {
                                return '<span class="item">'.$data->increment_id.'</span>';;
                            },
                            'format' => 'raw',
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '产品编号',
                            'attribute' => 'product_id',
                            'contentOptions'=>['style'=>'min-width: 100px;'],
                            'value' => function ($data) {
                                $items = $data->items;
                                $output = '';
                                foreach($items as $item){
                                    $output.='<span class="item">'.$item->product_id.'</span>';
                                }
                                return $output;
                            },
                            'format' => 'raw',
                        ],
                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => 'SKU',
                            'attribute' => 'sku',
                            'contentOptions'=>['style'=>'min-width: 100px;'],
                            'value' => function ($data) {
                                $items = $data->items;
                                $output = '';
                                foreach($items as $item){
                                    $output.='<span class="item">'.$item->sku.'</span>';
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
                                $items = $data->items;
                                $output = '';
                                $products = $data->products;
                                foreach($items as $item){
                                    $image = $products[$item->product_id]->getMasterImage();
                                    $output .=\renk\yiipal\helpers\FileHelper::getThumbnailWithLink($image,$item->id);
                                }
                                return $output;
                            },
                            'format'=>'raw',
                            'enableSorting'=>false,
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'contentOptions'=>['style'=>'max-width: 45px;'],
                            'label' => '分发',
                            'filter'=>function($data){
                                return ['custom'=>'定制','taobao'=>'淘宝','stock'=>'库存'];
                            },
                            'attribute' => 'item_type',
                            'value' => function ($data) {
                                $item_types = ['custom'=>'定制','taobao'=>'淘宝','stock'=>'库存'];
                                $items = $data->items;
                                $output = '';
                                foreach($items as $item){
                                    $output .= '<span class="item">'.$item_types[$item->item_type].'</span>';
                                }
                                return $output;
                            },
                            'format' => 'raw',
                            'enableSorting'=>false,
                        ],


                        [
                            'class' => 'yii\grid\DataColumn',
                            'contentOptions'=>['style'=>'min-width: 55px;'],
                            'label' => '类型',
                            'filter'=>function($data){
                                return ['taobao'=>'淘宝款','factory'=>'工厂款','virtual'=>'虚拟产品'];
                            },
                            'attribute' => 'product_type',
                            'value' => function ($data) {
                                $item_types = ['factory'=>'工厂款','taobao'=>'淘宝款','virtual'=>'虚拟款'];
                                $items = $data->items;
                                $products = $data->products;
                                $output = '';
                                foreach($items as $item){
                                    $output .= '<span class="item">'.$item_types[$products[$item->product_id]->type].'</span>';
                                }
                                return $output;
                            },
                            'format' => 'raw',
                            'enableSorting'=>false,
                        ],


                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '订单金额',
                            'attribute' => 'grand_total',
                            'visible'=>function(){
                                return Yii::$app->user->can('/permission/order-price');
                            },
                            'value' => function ($data) {
                                return '<span class="item">'.round($data->grand_total,2).'('.$data->currency_code.')'.'</span>';
                            },
                            'format' => 'raw',
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '物流',
                            'contentOptions'=>['style'=>'max-width: 45px;'],
                            'filter'=>function($data){
                                return \app\helpers\Options::shippingMethods();
                            },
                            'attribute' => 'shipping_method',
                            'value' => function ($data) {
                                return '<span class="item">'.$data->shipping_method.'</span>';
                            },
                            'format' => 'raw',
                        ],

                        [
                            'class' => 'renk\yiipal\grid\DataColumn',
                            'label' => '创建时间',
                            'contentOptions'=>['style'=>'max-width: 150px;min-width: 85px;'],
                            'filter'=>function($model){
                                $gets = Yii::$app->request->get('Order',[]);
                                $created_at = isset($gets['created_at'])?$gets['created_at']:'';
                                $output = '<input type="text" class="form-control form-daterange pull-right" name="Order[created_at]" value="'.$created_at.'"/>';
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
                            'label' => '发货时间',
                            'contentOptions'=>['style'=>'max-width: 150px;min-width: 85px;','class'=>'col-shipped_at'],
                            'headerOptions'=>['class'=>'col-shipped_at'],
                            'filterOptions'=>['class'=>'col-shipped_at'],
                            'filter'=>function($model){
                                $gets = Yii::$app->request->get('Order',[]);
                                $shipped_at = isset($gets['shipped_at'])?$gets['shipped_at']:'';
                                $output = '<input type="text" class="form-control form-daterange pull-right" name="Order[shipped_at]" value="'.$shipped_at.'"/>';
                                return $output;
                            },
                            'attribute' => 'shipped_at',
                            'value' => function ($data) {
                                if(empty($data->shipped_at)){
                                    return '<span class="item">未发货</span>';
                                }else{
                                    return '<span class="item">'.date("Y-m-d H:i:s",$data->shipped_at).'</span>';
                                }

                            },
                            'format' => 'raw',
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '支付状态',
                            'contentOptions'=>['style'=>'max-width: 75px;'],
                            'filter'=>ItemStatus::paymentStatusOptions(),
                            'attribute' => 'payment_status',
                            'value' => function ($data) {
                                $fontColor = '';
                                if($data->payment_status=='fraud'){
                                    $fontColor = 'text-danger';
                                }
                                $statusLabel = '<span class="payment-status-text">'.ItemStatus::paymentStatusOptions($data->payment_status).'</span>';
                                if(Yii::$app->user->can('/order/order/order-payment-status-tracking')){
                                    $output = Html::a($statusLabel,'/order/order/order-payment-status-tracking?id='.$data->id,['class'=>'ajax-modal','title'=>'支付历程']);
                                }else{
                                    $output = $statusLabel;
                                }

                                if(in_array($data->payment_status,['paypal_reversed','paypal_canceled_reversal'])){
                                    $url = "/order/order/edit-order-payment-status";
                                    if(Yii::$app->user->can($url)){
                                        $url .="?id=".$data->id;
                                        $output .= ' <a href="'.$url.'" class="ajax-change-status" title="修改为Processing"><i class="glyphicon glyphicon-refresh"></i></a>';
                                    }
                                }

                                return '<span class="item '.$fontColor.'">'.$output.'</span>';
                            },
                            'format' => 'raw',
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '处理状态',
                            'contentOptions'=>['style'=>'max-width: 75px;'],
                            'filter'=>ItemStatus::allStatus(),
                            'attribute' => 'status',
                            'value' => function ($data) {
                                $fontColor = '';
                                if($data->status=='cancelled'){
                                    $fontColor = 'text-danger';
                                }
                                if(Yii::$app->user->can('/order/order/order-status-tracking')){
                                    $output = Html::a(ItemStatus::allStatus($data->status),['/order/order/order-status-tracking?id='.$data->id],['class'=>'ajax-modal','title'=>'状态历程']);
                                }else{
                                    $output = ItemStatus::allStatus($data->status);
                                }

                                return '<span class="item '.$fontColor.'">'.$output.'</span>';
                            },
                            'format' => 'raw',
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '跟踪状态',
                            'contentOptions'=>['style'=>'max-width: 75px;'],
                            'filter'=>ItemStatus::trackStatus(),
                            'attribute' => 'last_track_status',
                            'value' => function ($data) {
                                $comments = $data->comments;
                                $list = \app\helpers\CommonHelper::getOrderChangeHistory($comments);
                                $fontColor = 'text-danger';
                                $output = Html::a(ItemStatus::TrackStatus($data->last_track_status),['comment/list?target_id='.$data->id.'&type=order&group=confirm'],['class'=>'ajax-modal text-danger','title'=>'备注']);
                                if($data->last_track_status == \app\models\order\Order::TASK_STATUS_NORMAL){
                                    $fontColor = '';
                                    $output = ItemStatus::TrackStatus($data->last_track_status);
                                }
                                if($data->last_track_status == \app\models\order\Order::TASK_STATUS_CHANGE_CONFIRMED){
                                    $fontColor = 'text-success font-bold';
                                    $output = Html::a(ItemStatus::TrackStatus($data->last_track_status),['comment/list?target_id='.$data->id.'&type=order&group=confirm'],['class'=>'ajax-modal text-success font-bold','title'=>'备注']);
                                }
                                return $list.'<span class="item '.$fontColor.'">'.$output.'</span>';
                            },
                            'format' => 'raw',
                        ],
                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '产品状态',
                            'contentOptions'=>['style'=>'max-width: 75px;','class'=>'col-item_status'],
                            'headerOptions'=>['class'=>'col-item_status'],
                            'filterOptions'=>['class'=>'col-item_status'],
                            'filter'=>false,
                            'attribute' => 'item_status',
                            'value' => function ($data) {
                                $items = $data->items;
                                $output = '';
                                foreach($items as $item){
                                    $output .= '<span class="item">'.ItemStatus::allStatus($item->item_status).'</span>';
                                }
                                return $output;
                            },
                            'format' => 'raw',
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '运单',
                            'contentOptions'=>['style'=>'max-width: 45px;','class'=>'col-has_shipment'],
                            'headerOptions'=>['class'=>'col-has_shipment'],
                            'filterOptions'=>['class'=>'col-has_shipment'],
                            'filter'=>['1'=>'有','0'=>'无'],
                            'attribute' => 'has_shipment',
                            'value' => function ($data) {
                                $label = '无';
                                if($data->has_shipment==1){
                                    $label = '有';
                                }
                                return '<span class="item">'.$label.'</span>';
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
                                if($data->paused==1){
                                    $label = '产品';
                                    $class = 'text-red';
                                }
                                if($data->paused==2){
                                    $label = '地址';
                                    $class = 'text-red';
                                }
                                return '<span class="item '.$class.'">'.$label.'</span>';
                            },
                            'format' => 'raw',
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '订单类型',
                            'contentOptions'=>['style'=>'max-width: 75px;','class'=>'col-order_type'],
                            'headerOptions'=>['class'=>'col-order_type'],
                            'filterOptions'=>['class'=>'col-order_type'],
                            'filter'=>ItemStatus::orderTypeOptions(),
                            'attribute' => 'order_type',
                            'value' => function ($data) {
                                return '<span class="item">'.ItemStatus::orderTypeOptions($data->order_type).'</span>';
                            },
                            'format' => 'raw',
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '来源',
                            'contentOptions'=>['style'=>'max-width: 45px;min-width: 45px;width: 45px;','class'=>'col-source'],
                            'headerOptions'=>['class'=>'col-source'],
                            'filterOptions'=>['class'=>'col-source'],
                            'filter'=>\app\helpers\Options::websiteOptions(),
                            'attribute' => 'source',
                            'value' => function ($data) {
                                return '<span class="item">'.strtoupper($data->source).'</span>';
                            },
                            'format' => 'raw',
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '客户端',
                            'contentOptions'=>['style'=>'max-width: 75px;','class'=>'col-from'],
                            'headerOptions'=>['class'=>'col-from'],
                            'filterOptions'=>['class'=>'col-from'],
                            'attribute' => 'from',
                            'filter'=>['pc'=>'PC','mobile'=>'Mobile'],
                            'value' => function ($data) {
                                $output = 'PC';
                                if($data->from == 'mobile'){
                                    $output = 'Mobile';
                                }
                                return '<span class="item">'.$output.'</span>';
                            },
                            'format' => 'raw',
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '备注',
                            'contentOptions'=>['style'=>'max-width: 75px;'],
                            'filter'=>['1'=>'有','-1'=>'无'],
                            'attribute' => 'has_comment',
                            'value' => function ($data) {
                                $output = Html::a('有',['comment/list?target_id='.$data->id.'&type=order'],['class'=>'ajax-modal click-green text-white','title'=>'备注']);
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
                            'label' => '优惠券',
                            'contentOptions'=>['style'=>'max-width: 75px;','class'=>'col-coupon_code'],
                            'headerOptions'=>['class'=>'col-coupon_code'],
                            'filterOptions'=>['class'=>'col-coupon_code'],
                            'visible'=>function(){
                                return Yii::$app->user->can('/permission/order-service-column');
                            },
                            'attribute' => 'coupon_code',
                            'value' => function ($data) {
                                return '<span class="item ">'.str_replace('NULL','',$data->coupon_code).'</span>';
                            },
                            'format' => 'raw',
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '客户姓名',
                            'contentOptions'=>['style'=>'max-width: 75px;','class'=>'col-customer_name'],
                            'headerOptions'=>['class'=>'col-customer_name'],
                            'filterOptions'=>['class'=>'col-customer_name'],
                            'visible'=>function(){
                                return Yii::$app->user->can('/permission/order-service-column');
                            },
                            'attribute' => 'customer_name',
                            'value' => function ($data) {
                                $output = '';
                                if(isset($data->address)){
                                    $output = $data->address->firstname.' '.$data->address->lastname;
                                }
                                $output = str_replace('NULL','',$output);
                                return '<span class="item ">'.$output.'</span>';
                            },
                            'format' => 'raw',
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '客户邮箱',
                            'contentOptions'=>['style'=>'max-width: 75px;','class'=>'col-customer_email'],
                            'headerOptions'=>['class'=>'col-customer_email'],
                            'filterOptions'=>['class'=>'col-customer_email'],
                            'visible'=>function(){
                                return Yii::$app->user->can('/permission/order-service-column');
                            },
                            'attribute' => 'customer_email',
                            'value' => function ($data) {
                                $output = '';
                                if(isset($data->address)){
                                    $output = $data->address->email;
                                }
                                return '<span class="item ">'.$output.'</span>';
                            },
                            'format' => 'raw',
                        ],

                        [
                            'class' => 'renk\yiipal\grid\ActionColumn',
                            'contentOptions'=>['style'=>'min-width: 75px;max-width: 75px;width: 75px;'],
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>
</div>
<?php
$js = <<<JS
var filterColsList = $filterColsList;
$(document).on("click",".ajax-change-status",function(){
    var url = $(this).attr("href");
    var that = this;
    $.get(url,function(response){
        $(".overlay").hide();
        if(response.status == '00'){
            $(that).parent().text("Processing");
        }
        bootbox.alert(response.msg);
    });
    return false;
});

$(document).on("click",'.filter-cols-checkbox input',function(){
    var selector = "."+$(this).val();
    var filter_cols = Cookies.getJSON('filter_cols');
    if(filter_cols == undefined){
        filter_cols = {};
    }
    if($(this).is(":checked")){
        $(selector).hide();
        filter_cols[$(this).val()] = 'hide';
    }else{
        $(selector).show();
        filter_cols[$(this).val()] = 'show';
    }
    Cookies.set('filter_cols',filter_cols);
});
var filter_cols = Cookies.getJSON('filter_cols');
if(filter_cols == undefined){
    filter_cols = {};
    $.each(filterColsList,function(index,col){
        filter_cols[col] = 'hide';
    });
}
$.each(filter_cols,function(key, item){
    if(item == 'hide'){
        $('.'+key).hide();
        $('.filter-cols-checkbox input[value='+key+']').attr("checked","checked");
    }else{
        $('.'+key).show();
        $('.filter-cols-checkbox input[value='+key+']').removeAttr("checked");
    }
});
JS;

$this->registerJs($js);
?>