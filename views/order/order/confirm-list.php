<?php
use renk\yiipal\helpers\Html;
use yii\helpers\Url;
use renk\yiipal\grid\GridView;
use renk\yiipal\helpers\FileHelper;
use yii\bootstrap\ActiveForm;
use app\helpers\ItemStatus;
/* @var $this yii\web\View */

?>
<div class="order-index">
    <div class="body-content">
        <div class="row btn-group-top">
            <div class="col-xs-12">
                <div class="btn-group pull-right">
                    <div class="btn-group-top">
                        <?= Html::authSubmitButton('导出UPS模板', ['/shipment/export/export-ups-tpl'], ['class' => 'btn btn-default download','form-class'=>'ajax-form download', 'data-action-before'=>'get_ids']) ?>
                        <?= Html::authSubmitButton('导出DHL模板', ['/shipment/export/export-dhl-tpl'], ['class' => 'btn btn-default download','form-class'=>'ajax-form download', 'data-action-before'=>'get_ids']) ?>
                        <?= Html::authSubmitButton('导出EUB模板', ['/shipment/export/export-eub-tpl'], ['class' => 'btn btn-default download','form-class'=>'ajax-form download', 'data-action-before'=>'get_ids']) ?>
                        <?= Html::authSubmitButton('确认变更', ['change-confirm'], ['class' => 'btn btn-primary confirm','form-class'=>'ajax-form', 'data-action-before'=>'get_ids']) ?>
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
                                return '<span class="item">'.$data->ext_order_id.'</span>';;
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
                                $output = '<span class="item">'.$data->shipping_method.'</span>';
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
                            'class' => 'renk\yiipal\grid\DataColumn',
                            'label' => '发货时间',
                            'contentOptions'=>['style'=>'max-width: 150px;min-width: 85px;'],
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
                                return '<span class="item">'.$data->payment_status.'</span>';
                            },
                            'format' => 'raw',
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '处理状态',
                            'contentOptions'=>['style'=>'max-width: 75px;'],
                            'filter'=>ItemStatus::allStatus(),
                            'attribute' => 'item_status',
                            'value' => function ($data) {
                                $fontColor = '';
                                if($data->status=='cancelled'){
                                    $fontColor = 'text-danger';
                                }
                                return '<span class="item '.$fontColor.'">'.ItemStatus::allStatus($data->status).'</span>';
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
                            'label' => '运单',
                            'contentOptions'=>['style'=>'max-width: 45px;'],
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
                            'contentOptions'=>['style'=>'max-width: 45px;'],
                            'filter'=>['1'=>'是','0'=>'否'],
                            'attribute' => 'paused',
                            'value' => function ($data) {
                                $label = '否';
                                $class = '';
                                if($data->paused>=1){
                                    $label = '是';
                                    $class = 'text-red';
                                }
                                return '<span class="item '.$class.'">'.$label.'</span>';
                            },
                            'format' => 'raw',
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '订单类型',
                            'contentOptions'=>['style'=>'max-width: 75px;'],
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
                            'contentOptions'=>['style'=>'max-width: 75px;'],
                            'attribute' => 'source',
                            'value' => function ($data) {
                                return '<span class="item">'.strtoupper($data->source).'</span>';
                            },
                            'format' => 'raw',
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '备注',
                            'contentOptions'=>['style'=>'max-width: 75px;'],
                            'attribute' => 'comment',
                            'value' => function ($data) {
                                $output = Html::a('有',['comment/list?target_id='.$data->id.'&type=order'],['class'=>'ajax-modal','title'=>'备注']);
                                if(empty($data->comments)){
                                    $output = '无';
                                }
                                return '<span class="item">'.$output.'</span>';
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
