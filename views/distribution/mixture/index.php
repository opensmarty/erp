<?php
use renk\yiipal\helpers\Html;
use yii\helpers\Url;
use renk\yiipal\grid\GridView;
use renk\yiipal\helpers\FileHelper;
use yii\bootstrap\ActiveForm;
use app\helpers\ItemStatus;
use app\helpers\CommonHelper;
/* @var $this yii\web\View */


function renderDataCellContent($model)
{
    $buttons = $model->buttons();

    $buttonList = '';
    foreach($buttons as $button){
        $attr = $button['attr'];
        $class = '';
        if(isset($attr['class'])){
            $class = $attr['class'];
        }
        $data = '';
        if(isset($attr['data'])){
            $data = "data='".json_encode($attr['data'])."'";
        }
        $urlInfo =parse_url($button['url']);
        if (Yii::$app->user->can($urlInfo['path'], isset($urlInfo['query']))?:[]) {
            $buttonList.='<li><a href="'.$button['url'].'" '.$data.' class="'.$class.'">'.$button['label'].'</a></li>';
        }
    }

    if(empty($buttonList)){
        return '';
    }
    $output = '<div class="btn-group">
                      <button type="button" class="btn btn-default btn-xs btn-flat ">操作</button>
                      <button type="button" class="btn btn-default btn-xs btn-flat dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                        <span class="caret"></span>
                        <span class="sr-only">切换</span>
                      </button>
                      <ul class="dropdown-menu" role="menu">
                        '.$buttonList.'
                      </ul>
                </div>';
    return $output;
}
?>
<div class="order-index">
    <div class="body-content">
        <div class="row btn-group-top">
            <div class="col-xs-12">
                <div class="btn-group pull-right">
                    <div class="btn-group-top">
                        <?= Html::authSubmitButton('导出', ['export'], ['class' => 'btn btn-default download','form-class'=>'ajax-form download', 'data-action-before'=>'get_ids']) ?>
                        <?= Html::authSubmitButton('发货', ['/distribution/custom/ship'], ['class' => 'btn btn-danger download','form-class'=>'ajax-form download', 'data-action-before'=>'get_ids'],['type'=>'order']) ?>
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
                                if($data->expedited == 1){
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
                            'label' => '物流',
                            'contentOptions'=>['style'=>'max-width: 45px;min-width:45px;'],
                            'filter'=>function($data){
                                return \app\helpers\Options::shippingMethods();
                            },
                            'attribute' => 'shipping_method',
                            'value' => function ($data) {
                                $output = '';
                                $url = '/order/order/edit-shipping-info';
                                if(CommonHelper::canEditOrder($data) && Yii::$app->user->can(Url::to($url))){
                                    $output .='<span class="item"><a href="#" data-name="shipping_method" class="edit-shipping-method" data-type="select" data-pk="'.$data->id.'" data-url="/order/order/edit-shipping-info" data-title="物流公司">'.$data->shipping_method.'<i class="glyphicon glyphicon-pencil"></i></a></span>';
                                }else{
                                    $output = '<span class="item">'.$data->shipping_method.'</span>';
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
                                if(CommonHelper::canEditOrder($data) && Yii::$app->user->can(Url::to($url))){
                                    $output .='<span class="item"><a href="#" data-name="shipping_track_no" class="editable-text" data-type="text" data-pk="'.$data->id.'" data-url="/order/order/edit-shipping-info" data-title="物流单号">'.$data->shipping_track_no.'<i class="glyphicon glyphicon-pencil"></i></a></span>';
                                }else{
                                    $output = '<span class="item">'.$data->shipping_track_no.'</span>';
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
                                foreach($items as $item){
                                    if(!$item->canDistribute())continue;
                                    $image = $item->product->getMasterImage();
                                    $output .= \renk\yiipal\helpers\FileHelper::getThumbnailWithLink($image,$item->id);
                                }
                                return $output;
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
                            'label' => '产品编号',
                            'attribute' => 'product_id',
                            'value' => function ($data) {
                                $items = $data->items;
                                $output = '';
                                foreach($items as $item){
                                    if(!$item->canDistribute())continue;
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
                            'value' => function ($data) {
                                $items = $data->items;
                                $output = '';
                                foreach($items as $item){
                                    if(!$item->canDistribute())continue;
                                    $output.='<span class="item">'.$item->sku.'</span>';
                                }
                                return $output;
                            },
                            'format' => 'raw',
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '尺码|刻字',
                            'contentOptions'=>['style'=>'min-width: 170px;max-width: 170px;'],
                            'attribute' => 'product_options',
                            'value' => function ($data) {
                                $items = $data->items;
                                $output = '';
                                foreach($items as $item){
                                    if(!$item->canDistribute())continue;
                                    $output .= '<span class="item">';
                                    if(!empty($item->engravings)){
                                        $output .= '刻字:'.$item->engravings.'<br/>';
                                    }
                                    if(!empty($item->size_us)){
                                        $output.= '网站尺码:'.$item->size_original.'<br/>';
                                        $output.= '实际尺码:'.$item->size_us;
                                    }
                                    if($item->size_type != 'none'){
                                        $output.='['.\app\helpers\Options::ringTypes($item->size_type).']';
                                    }

                                    $output.= '</span>';
                                }
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
                                $items = $data->items;
                                $output = '';
                                foreach($items as $item){
                                    if(!$item->canDistribute())continue;
                                    if(empty($item->engravings)){
                                        $result = '无';
                                    }else{
                                        $result = '有';
                                    }
                                    $output.='<span class="item">'.$result.'</span>';
                                }
                                return $output;
                            },
                            'format' => 'raw',
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
                                    if(!$item->canDistribute())continue;
                                    $output .= '<span class="item">'.$item_types[$item->item_type].'</span>';
                                }
                                return $output;
                            },
                            'format' => 'raw',
                            'enableSorting'=>false,
                        ],


                        [
                            'class' => 'yii\grid\DataColumn',
                            'contentOptions'=>['style'=>'width: 55px;'],
                            'label' => '类型',
                            'filter'=>function($data){
                                return ['taobao'=>'淘宝款','factory'=>'工厂款','virtual'=>'虚拟产品'];
                            },
                            'attribute' => 'product_type',
                            'value' => function ($data) {
                                $item_types = ['factory'=>'工厂款','taobao'=>'淘宝款','virtual'=>'虚拟款'];
                                $items = $data->items;
                                $output = '';
                                foreach($items as $item){
                                    if(!$item->canDistribute())continue;
                                    if($item->product->type == 'taobao' && !empty($item->product->taobao_url)){
                                        $output .='<span class="item"><a href="'.$item->product->taobao_url.'" target="_blank">'.$item_types[$item->product->type].'</a></span>';
                                    }else{
                                        $output .='<span class="item">'.$item_types[$item->product->type].'</span>';
                                    }

//                                    return $output;
//                                    $output .= '<span class="item">'.$item_types[$item->product->type].'</span>';
                                }
                                return $output;
                            },
                            'format' => 'raw',
                            'enableSorting'=>false,
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '数量',
                            'attribute' => 'qty_ordered',
                            'value' => function ($data) {
                                $items = $data->items;
                                $output = '';
                                foreach($items as $item){
                                    if(!$item->canDistribute())continue;
                                    $labelDanger = '';
                                    if($item->qty_ordered>1){
                                        $labelDanger = 'label-danger';
                                    }
                                    $output .= '<span class="item '.$labelDanger.'">'.$item->qty_ordered.'</span>';
                                }
                                return $output;
                            },
                            'format' => 'raw',
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '库存',
                            'attribute' => 'qty_delivery',
                            'value' => function ($data) {
                                $items = $data->items;
                                $output = '';
                                foreach($items as $item){
                                    if(!$item->canDistribute())continue;
                                    $stockInfo = isset($data->allStocks[$item->product_id])?$data->allStocks[$item->product_id]:[];
                                    $stocks = CommonHelper::getStocksBy($stockInfo,$item->size_us,$item->size_type);
                                    $output.='<span class="item">'.$stocks['actual_total'].'('.intval($stocks['virtual_total']).')'.'</span>';

                                }
                                return $output;
                            },
                            'format' => 'raw',
                        ],
                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '验收',
                            'contentOptions'=>['style'=>'max-width: 65px;'],
                            'filter'=>function($data){
                                return ['1'=>'次品','0'=>'正常'];
                            },
                            'attribute' => 'has_rejects',
                            'value' => function ($data) {

                                $items = $data->items;
                                $output = '';
                                foreach($items as $item){
                                    $text= '正常';
                                    $fontColor = 'label-success';
                                    if($item->has_rejects == 1){
                                        $text= '次品';
                                        $fontColor = 'label-danger';
                                    }
                                    $output .= '<span class="item">'.'<small class="label '.$fontColor.'"><i class="fa fa-check-circle"></i>'.$text.'</small>'.'</span>';
                                }
                                return $output;
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
                            'label' => '处理状态',
                            'contentOptions'=>['style'=>'max-width: 75px;'],
                            'filter'=>ItemStatus::allStatus(),
                            'attribute' => 'status',
                            'value' => function ($data) {
                                //return '<span class="item">'.ItemStatus::allStatus($data->status).'</span>';
                                
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
                                $fontColor = 'text-danger';
                                $output = Html::a(ItemStatus::TrackStatus($data->last_track_status),['comment/list?target_id='.$data->id.'&type=order&group=confirm'],['class'=>'ajax-modal text-danger','title'=>'备注']);
                                if($data->last_track_status == \app\models\order\Order::TASK_STATUS_NORMAL){
                                    $fontColor = '';
                                    $output = ItemStatus::TrackStatus($data->last_track_status);
                                }
                                if($data->last_track_status == \app\models\order\Order::TASK_STATUS_CHANGE_CONFIRMED){
                                    $fontColor = 'text-success';
                                }
                                return '<span class="item '.$fontColor.'">'.$output.'</span>';
                            },
                            'format' => 'raw',
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '产品状态',
                            'contentOptions'=>['style'=>'max-width: 75px;'],
                            'filter'=>ItemStatus::allStatus(),
                            'attribute' => 'item_status',
                            'value' => function ($data) {
                                $items = $data->items;
                                $output = '';
                                foreach($items as $item){
                                    if(!$item->canDistribute())continue;
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
                            'class' => 'renk\yiipal\grid\ActionColumn',
                            'contentOptions'=>['style'=>'min-width: 75px;'],
                            'customButtons' => function($model, $key, $index){
                                $output = '';
                                $items = $model->items;
                                foreach($items as $item){
                                    if(!$item->canDistribute())continue;
                                    $button = renderDataCellContent($item);
                                    $output .= '<span class="item">'.$button.'</span>';
                                }
                                return $output;
                            },
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