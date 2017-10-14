<?php
use renk\yiipal\helpers\Html;
use yii\helpers\Url;
use renk\yiipal\grid\GridView;
use app\helpers\Options;
use yii\widgets\Pjax;
use yii\bootstrap\ActiveForm;
/* @var $this yii\web\View */
?>
<div class="distribution-index">
    <div class="body-content">
        <div class="row btn-group-top">
            <div class="col-xs-12">
                <div class="btn-group pull-right">

                </div>
            </div>
            <div class="col-xs-6">
            </div>
        </div>

        <div class="row">
            <div class="col-lg-3 col-xs-6">
                <!-- small box -->
                <div class="small-box bg-green">
                    <div class="inner">
                        <h3><?=$staticInfo['total_number'];?></h3>

                        <p>累计发货</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-bag"></i>
                    </div>
                </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-xs-6">
                <!-- small box -->
                <div class="small-box bg-aqua">
                    <div class="inner">
                        <h3><?=$staticInfo['wrong_number'];?></h3>

                        <p>累计错误</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-stats-bars"></i>
                    </div>
                </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-xs-6">
                <!-- small box -->
                <div class="small-box bg-yellow">
                    <div class="inner">
                        <h3><?=$staticInfo['wrong_rate'];?><sup style="font-size: 20px">%</sup></h3>

                        <p>错误率</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-person-add"></i>
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
                        ['class' => 'yii\grid\SerialColumn'],
                        'ext_order_id',
                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '状态',
                            'attribute' => 'status',
                            'filter'=>Options::shipmentTypes(),
                            'value' => function ($data) {
                                $fontColor = '';
                                if($data->status !='normal'){
                                    $fontColor = 'text-danger';
                                }
                                return '<span class="item '.$fontColor.'">'.Options::shipmentTypes($data->status).'</span>';
                            },
                            'format'=>'raw',
                            'enableSorting'=>false,
                        ],
                        [
                            'class' => 'renk\yiipal\grid\DataColumn',
                            'label' => '发货时间',
                            'attribute' => 'created_at',
                            'filter'=>function($model){
                                $gets = Yii::$app->request->get('ShipmentLog',[]);
                                $created_at = isset($gets['created_at'])?$gets['created_at']:'';
                                $output = '<input type="text" class="form-control form-daterange pull-right" name="ShipmentLog[created_at]" value="'.$created_at.'"/>';
                                return $output;
                            },
                            'value' => function ($data) {
                                return date("Y-m-d H:i:s",$data->created_at);
                            },
                            'format'=>'raw',
                            'enableSorting'=>true,
                        ],
                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '发货人',
                            'attribute' => 'ship_uid',
//                            'filter'=>true,
                            'value' => function ($data) {
                                if($data->shipmentUser){
                                    $output = '<span class="item">'.$data->shipmentUser->nick_name.'</span>';
                                    return $output;
                                }else{
                                    return null;
                                }

                            },
                            'format'=>'raw',
                            'enableSorting'=>false,
                        ],
                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '错误报告人',
                            'attribute' => 'report_uid',
//                            'filter'=>false,
                            'value' => function ($data) {
                                if($data->reportUser){
                                    $output = '';
                                    $url = '/dashboard/distribution/note';
                                    if(Yii::$app->user->can(Url::to($url))){
                                        $link = Html::a($data->reportUser->nick_name,[$url.'?id='.$data->id],['class'=>'ajax-modal','title'=>'错误原因']);
                                        $output .='<span class="item">'.$link.'</span>';
                                    }else{
                                        $output = '<span class="item">'.$data->reportUser->nick_name.'</span>';
                                    }
                                    return $output;
                                }else{
                                    return null;
                                }

                            },
                            'format'=>'raw',
                            'enableSorting'=>false,
                        ],
                        [
                            'class' => 'renk\yiipal\grid\DataColumn',
                            'label' => '错误记录时间',
                            'attribute' => 'updated_at',
                            'filter'=>function($model){
                                $gets = Yii::$app->request->get('ShipmentLog',[]);
                                $updated_at = isset($gets['updated_at'])?$gets['updated_at']:'';
                                $output = '<input type="text" class="form-control form-daterange pull-right" name="ShipmentLog[updated_at]" value="'.$updated_at.'"/>';
                                return $output;
                            },
                            'value' => function ($data) {
                                if($data->status != 'normal'){
                                    return date("Y-m-d H:i:s",$data->updated_at);
                                }else{
                                    return null;
                                }
                            },
                            'format'=>'raw',
                            'enableSorting'=>true,
                        ],
                    ],
                ]); ?>
            </div>
        </div>

    </div>
</div>
