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
$css = <<<CSS
div.list-row{
    min-height: 400px;
}
span#filter_display_tags{
    display: inline-block;
}
CSS;
$this->registerCss($css);
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
                                return ['custom'=>'定制','taobao'=>'淘宝','stock'=>'库存'];
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
                                    $fontColor = 'text-success';
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
                            'label' => '备注',
                            'contentOptions'=>['style'=>'max-width: 75px;'],
                            'filter'=>['1'=>'有','-1'=>'无'],
                            'attribute' => 'has_comment',
                            'value' => function ($data) {
                                $output = Html::a('有',['comment/list?target_id='.$data->id.'&type=order'],['class'=>'ajax-modal text-white','title'=>'备注']);
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
                            'class' => 'renk\yiipal\grid\DataColumn',
                            'label' => '订单问题',
                            'contentOptions'=>['style'=>'max-width: 75px;'],
                            'attribute' => 'issue_tags',
                            'filter'=>function($data){
                                $categories = \app\models\Category::find()->indexBy('id')->asArray()->all();
                                $gets = Yii::$app->request->get('Order',[]);
                                $tags = [];
                                if(isset($gets['issue_tags'])){
                                    $tags = explode(",",$gets['issue_tags']);
                                }
                                $label = '';
                                foreach($tags as $tag){
                                    if(empty($tag)||!isset($categories[$tag]))continue;
                                    $label .= $categories[$tag]['name'].",";
                                }
                                $label = rtrim($label,',');
                                $label = $label?:'全部';
                                return '<div class="" style="position: relative;"><span id="filter_display_tags">'.$label.' </span><a href="javascript:;" id="filter_tags_select"><span class="glyphicon glyphicon-pencil"></span></a><input type="hidden" name="Order[issue_tags]" value="" id="filter_tags"/><div id="issue_tags_tree" style="position: absolute;top:42px;left:0;background: #FFF;border: solid 1px gray;"></div></div>';
                            },
                            'value' => function ($data) {
                                if(!isset($data->orderIssue)){
                                    return '';
                                }
                                $tags = explode(',',$data->orderIssue->issue_tags);
                                $output = '';
                                foreach($tags as $tag){
                                    if(empty($tag)||!isset($data->categories[$tag]))continue;
                                    $output = $output.$data->categories[$tag]['name'].",";
                                }
                                return rtrim($output,',');
                            },
                            'format' => 'raw',
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '处理状态',
                            'attribute' => 'issue_status',
                            'filter'=>['pending'=>'未解决','solved'=>'已解决','processing'=>'解决中','wait_confirm'=>'待确认'],
                            'value' => function ($data) {
                                $options = ['pending'=>'未解决','solved'=>'已解决','processing'=>'解决中','wait_confirm'=>'待确认'];
                                $fontColor = '';
                                if($data->orderIssue->issue_status == 'solved'){
                                    $fontColor = 'text-success';
                                }elseif($data->orderIssue->issue_status == 'wait_confirm'){
                                    $fontColor = 'text-danger text-bold';
                                }
                                $output = '';
                                $output.='<span class="item '.$fontColor.'">'.$options[$data->orderIssue->issue_status].'</span>';
                                return $output;
                            },
                            'format' => 'raw',
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '报告人',
                            'attribute' => 'report_uid',
                            'value' => function ($data) {
                                if($data->orderIssue->reportUser){
                                    return $data->orderIssue->reportUser->nick_name;
                                }else{
                                    return null;
                                }
                            },
                            'format' => 'raw',
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '解决人',
                            'attribute' => 'solved_uid',
                            'value' => function ($data) {
                                if($data->orderIssue->solvedUser){
                                    return $data->orderIssue->solvedUser->nick_name;
                                }else{
                                    return null;
                                }
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
var selectedTags = [$tags];

$(window).resize(function () {
    var h = Math.max($(window).height() - 0, 420);
    $('#container, #data, #tree, #data .content').height(h).filter('.default').css('lineHeight', h + 'px');
}).resize();
var initTags = function(){
    $('#issue_tags_tree')
    .jstree({
        'core' : {
            'data' : {
                'url' : '/category/operation?operation=get_children&parent_id=109',
                'data' : function (node) {
                    if(node.id=='#'){
                        //问题 分类
                        return { 'id' : 109 };
                    }else{
                        return { 'id' : node.id};
                    }

                }
            },
            'check_callback' : true,
            'themes': {
                'name': 'default',
                'responsive': true
            }
        },
        'force_text' : true,
        'plugins' : ['checkbox','state','dnd']
    })
    .on('state_ready.jstree', function (e, data) {
        data.instance.uncheck_all();
        data.instance.check_node(selectedTags);

    })
    .on('activate_node.jstree',function(e,data){
        var nodes = data.instance.get_bottom_selected(true);
        var tags = '';
        var ids = '';
        $.each(nodes,function(){
            ids += this.id+",";
            tags += this.text+",";
        });
        $("#filter_tags").val(ids).change();
        //$("#filter_display_tags").text(tags);
    }).hide();
    ;
}
initTags();

$("#filter_tags_select").click(function(event){
    $("#issue_tags_tree").show();
    event.stopPropagation();

});
$("body").click(function(){
    $("#issue_tags_tree").hide();
});
JS;

$this->registerJs($js);
?>