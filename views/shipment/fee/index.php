<?php
use renk\yiipal\helpers\Html;
use yii\helpers\Url;
use renk\yiipal\grid\GridView;
use yii\bootstrap\ActiveForm;
/* @var $this yii\web\View */
?>
<?php

?>
<div class="order-index">
    <div class="body-content">
        <div class="row btn-group-top">
            <div class="col-xs-12">
                <div class="btn-group pull-right">
                    <div class="btn-group-top">
                        <?= Html::authLink('导入', ['import'], ['class' => 'btn btn-success']) ?>
                    </div>
                </div>
            </div>
            <div class="col-xs-6">
            </div>
        </div>

        <div class="row">
            <div class="col-xs-2">
                <!-- small box -->
                <div class="small-box bg-green">
                    <div class="inner">
                        <h3><?=$costInfo['total'];?></h3>

                        <p>总物流费用</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-bag"></i>
                    </div>
                </div>
            </div>
            <!-- ./col -->
            <div class="col-xs-2">
                <!-- small box -->
                <div class="small-box bg-aqua">
                    <div class="inner">
                        <h3><?=intval($costInfo['UPS']);?></h3>

                        <p>UPS</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-stats-bars"></i>
                    </div>
                </div>
            </div>
            <!-- ./col -->
            <div class="col-xs-2">
                <!-- small box -->
                <div class="small-box bg-aqua">
                    <div class="inner">
                        <h3><?=intval($costInfo['DHL']);?></h3>

                        <p>DHL</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-stats-bars"></i>
                    </div>
                </div>
            </div>
            <!-- ./col -->
            <div class="col-xs-2">
                <!-- small box -->
                <div class="small-box bg-aqua">
                    <div class="inner">
                        <h3><?=intval($costInfo['EUB']);?></h3>

                        <p>EUB</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-stats-bars"></i>
                    </div>
                </div>
            </div>
            <!-- ./col -->
            <div class="col-xs-2">
                <!-- small box -->
                <div class="small-box bg-aqua">
                    <div class="inner">
                        <h3><?=intval($costInfo['ARAMEX']);?></h3>

                        <p>ARAMEX</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-stats-bars"></i>
                    </div>
                </div>
            </div>
            <!-- ./col -->
        </div>

        <div class="row">
            <div class="col-xs-12">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'columns' => [
//                        ['class' => 'yii\grid\SerialColumn'],
                        [
                            'class' => 'yii\grid\DataColumn',
                            'contentOptions'=>['style'=>'max-width: 100px;min-width: 100px;width:100px;'],
                            'filterOptions'=>['style'=>'max-width: 100px;min-width: 100px;width:100px;'],
                            'label' => '批次',
                            'attribute' => 'batch_number',
                            'value' => function ($data) {
                                return $data->batch_number;
                            },
                            'format'=>'raw',
                        ],
                        [
                            'class' => 'yii\grid\DataColumn',
                            'contentOptions'=>['style'=>'max-width: 100px;min-width: 100px;width:100px;'],
                            'filterOptions'=>['style'=>'max-width: 100px;min-width: 100px;width:100px;'],
                            'label' => '物流方式',
                            'attribute' => 'shipping_method',
                            'filter'=>\app\helpers\Options::shippingMethods(),
                            'value' => function ($data) {
                                return $data->shipping_method;
                            },
                            'format'=>'raw',
                        ],
                        [
                            'class' => 'renk\yiipal\grid\DataColumn',
                            'contentOptions'=>['style'=>'max-width: 200px;min-width: 200px;width:200px;'],
                            'filterOptions'=>['style'=>'max-width: 200px;min-width: 200px;width:200px;'],
                            'label' => '导入时间',
                            'attribute' => 'created_at',
                            'filter'=>function($model){
                                $gets = Yii::$app->request->get('ShipmentFeeGroup',[]);
                                $created_at = isset($gets['created_at'])?$gets['created_at']:'';
                                $output = '<input type="text" class="form-control form-daterange pull-right" name="ShipmentFeeGroup[created_at]" value="'.$created_at.'"/>';
                                return $output;
                            },
                            'value' => function ($data) {
                                return date("Y-m-d H:i",$data->created_at);
                            },
                            'format'=>'raw',
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'contentOptions'=>['style'=>'max-width: 100px;min-width: 100px;width:100px;'],
                            'label' => '金额小计',
                            'attribute' => 'price',
                            'value' => function ($data) {
                                $items = $data->items;
                                $output = 0;
                                foreach($items as $item){
                                    $output += $item->price;
                                }
                                return $output;
                            },
                            'format'=>'raw',
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'contentOptions'=>['style'=>'max-width: 100px;min-width: 100px;width:100px;'],
                            'label' => '单号',
                            'attribute' => 'shipping_track_no',
                            'value' => function ($data) {
                                $items = $data->items;
                                $output = '';
                                foreach($items as $item){
                                    $output.='<span class="item">'.$item->shipping_track_no.'</span>';
                                }
                                return $output;
                            },
                            'format'=>'raw',
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'contentOptions'=>['style'=>'max-width: 100px;min-width: 100px;width:100px;'],
                            'label' => '订单号',
                            'attribute' => 'ext_order_id',
                            'value' => function ($data) {
                                $items = $data->items;
                                $output = '';
                                foreach($items as $item){
                                    $output.='<span class="item">'.Html::a($item->ext_order_id,Url::to(['/order/order/view','id'=>$item->order_id])).'</span>';
                                }
                                return $output;
                            },
                            'format'=>'raw',
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'contentOptions'=>['style'=>'max-width: 100px;min-width: 100px;width:100px;'],
                            'label' => '金额',
                            'attribute' => 'price',
                            'value' => function ($data) {
                                $items = $data->items;
                                $output = '';
                                foreach($items as $item){
                                    $output.='<span class="item">'.$item->price.'</span>';
                                }
                                return $output;
                            },
                            'format'=>'raw',
                        ],

                        [
                            'class' => 'renk\yiipal\grid\DataColumn',
                            'contentOptions'=>['style'=>'max-width: 100px;min-width: 100px;width:100px;'],
                            'label' => '发货时间',
                            'attribute' => 'shipped_at',
                            'filter'=>function($model){
                                $gets = Yii::$app->request->get('ShipmentFeeGroup',[]);
                                $shipped_at = isset($gets['shipped_at'])?$gets['shipped_at']:'';
                                $output = '<input type="text" class="form-control form-daterange pull-right" name="ShipmentFeeGroup[shipped_at]" value="'.$shipped_at.'"/>';
                                return $output;
                            },
                            'value' => function ($data) {
                                $items = $data->items;
                                $output = '';
                                foreach($items as $item){
                                    $output.='<span class="item">'.date("Y-m-d H:i",$item->shipped_at).'</span>';
                                }
                                return $output;
                            },
                            'format'=>'raw',
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
