<?php
use renk\yiipal\helpers\Html;
use yii\helpers\Url;
use renk\yiipal\grid\GridView;
use yii\bootstrap\ActiveForm;
/* @var $this yii\web\View */
//$this->title = '产品列表';
?>
<div class="product-index">
    <div class="body-content">
        <div class="row btn-group-top">
            <div class="col-xs-12">
                <div class="btn-group  pull-right">
                    <div class="btn-group-top">
                        <?= Html::authLink($ipFilter?"启用":"禁用", ['disabled'], ['class' => 'btn btn-danger ajax']) ?>
                        <?= Html::authLink('新增', ['create'], ['class' => 'btn btn-success']) ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    //'filterModel' => $searchModel,
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => 'IP地址',
                            'attribute' => 'ip',
                            'filter'=>false,
                            'value' => function ($data) {
                                return $data->ip;
                            },
                            'format'=>'raw',
                            'enableSorting'=>false,
                        ],
                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '状态',
                            'attribute' => 'permission',
                            'filter'=>false,
                            'value' => function ($data) {
                                return $data->permission;
                            },
                            'format'=>'raw',
                            'enableSorting'=>false,
                        ],
                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '用途',
                            'attribute' => 'type',
                            'filter'=>false,
                            'value' => function ($data) {
                                return $data->type;
                            },
                            'format'=>'raw',
                            'enableSorting'=>false,
                        ],
                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '设置人',
                            'attribute' => 'uid',
                            'filter'=>false,
                            'value' => function ($data) {
                                return $data->user->nick_name;
                            },
                            'format'=>'raw',
                            'enableSorting'=>false,
                        ],
                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '设置时间',
                            'attribute' => 'updated_at',
                            'filter'=>false,
                            'value' => function ($data) {
                                return date("Y-m-d H:i:s",$data->updated_at);
                            },
                            'format'=>'raw',
                            'enableSorting'=>false,
                        ],
                        [
                            'class' => 'renk\yiipal\grid\ActionColumn',
                            'template' => '{update} {delete}',
                            'headerOptions' => ["width" => "80"],
                        ],
                    ],
                ]); ?>
            </div>
        </div>

    </div>
</div>
