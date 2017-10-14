<?php
use renk\yiipal\helpers\Html;
use yii\helpers\Url;
use renk\yiipal\grid\GridView;
use yii\bootstrap\ActiveForm;
/* @var $this yii\web\View */
?>
<div class="product-index">
    <div class="body-content">
        <div class="row btn-group-top">
            <div class="col-xs-6">
                <div class="btn-group">
                    <div class="btn-group-top">
                        <?= Html::authLink('新增', ['create'], ['class' => 'btn btn-success']) ?>
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
                        ['class' => 'yii\grid\SerialColumn'],
                        'name',
                        'url',
                        'params',
                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '任务状态',
                            'attribute' => 'enabled',
                            'filter'=>['1'=>'启用','2'=>'关闭'],
                            'value' => function ($data) {
                                $options = ['1'=>'启用','2'=>'关闭'];
                                return $options[$data->enabled];
                            },
                            'format'=>'raw',
                        ],
                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '任务创建人',
                            'attribute' => 'uid',
                            'filter'=>false,
                            'value' => function ($data) {
                                return $data->user->nick_name;
                            },
                            'format'=>'raw',
                        ],
                        'last_run_time',
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
