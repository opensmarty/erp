<?php
use renk\yiipal\helpers\Html;
use renk\yiipal\grid\GridView;
use yii\widgets\Pjax;
use mdm\admin\components\Helper;

/* @var $this yii\web\View */
/* @var $searchModel mdm\admin\models\searchs\User */
/* @var $dataProvider yii\data\ActiveDataProvider */

?>
<div class="user-index">
    <div class="row btn-group-top">
        <div class="col-xs-12">
            <div class="btn-group pull-right">
                <div class="btn-group-top">
                    <?= Html::a('添加用户', ['create'], ['class' => 'btn btn-primary','form-class'=>'ajax-form']) ?>
                </div>
            </div>
        </div>
        <div class="col-xs-6">
        </div>
    </div>
    <?php Pjax::begin(); ?>
    <?=
    GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'nick_name',
            'username',
            [
                'class' => 'yii\grid\DataColumn',
                'label' =>'角色',
                'attribute' => 'item_name',
                'filter'=>function(){
                    $roles = \app\models\user\AuthItem::getRoles();
                    return \renk\yiipal\helpers\ArrayHelper::options($roles,'name','description');
                },
                'value' => function($model) {
                    $output = '';
                    $roles = $model->roles;
                    foreach($roles as $role){
                        $output .= $role->description.",";
                    }
                    $output = rtrim($output,",");
                    return $output;
                }
            ],
            'email:email',
//            'created_at:date',
            [
                'attribute' => 'status',
                'value' => function($model) {
                    return $model->status == 0 ? Yii::t('app','Inactive'): Yii::t('app','Active');
                },
                'filter' => [
                    0 => '禁用',
                    10 => '激活'
                ]
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{activate} {inactivate} {update} {assignment}',
                'buttons' => [
                    'update' => function($url, $model) {
                        if ($model->id == 1) {
                            return '';
                        }

                        return Html::a('<span class="glyphicon glyphicon-pencil"></span>', $url);
                    },
                    'activate' => function($url, $model) {
                        if ($model->status == 10 || $model->id == 1) {
                            return '';
                        }
                        $options = [
                            'title' => '激活',
                            'aria-label' => '激活',
                            'data-confirm' => '确认要激活该用户吗？',
                            'data-method' => 'post',
                            'data-pjax' => '0',
                        ];
                        return Html::a('<span class="glyphicon glyphicon-ok-circle"></span>', $url, $options);
                    },
                    'inactivate' => function($url, $model) {
                        if ($model->status == 0 || $model->id == 1) {
                            return '';
                        }
                        $options = [
                            'title' => '禁用',
                            'aria-label' => '禁用',
                            'data-confirm' => '确认要禁用该用户吗？',
                            'data-method' => 'post',
                            'data-pjax' => '0',
                        ];
                        return Html::a('<span class="glyphicon glyphicon-ban-circle"></span>', $url, $options);
                    },
                    'assignment' => function($url, $model) {
                        if ($model->id == 1) {
                            return '';
                        }
                        $url = '/user/assignment/view?id='.$model->id;
                        return Html::a('<span class="glyphicon glyphicon-lock"></span>', $url);
                    }

                ]
                ],
            ],
        ]);
        ?>
    <?php Pjax::end(); ?>
</div>
