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
                        'country',
                        'security_key',
                        'sender_email',
                        'service_email',
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
