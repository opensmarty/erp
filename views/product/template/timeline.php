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
    <div class="body-content content">
        <div class="row">
            <div class="col-xs-12">
                <table class="table table-striped table-bordered">
                    <thead>
                    <tr>
                        <th style="width: 100px;">状态</th>
                        <th style="max-width: 160px;">时间</th>
                        <th style="width: 75px;">操作人</th>
                    </tr>
                    </thead>
                    <tbody>
                        <tr class="<?= $model->finished_at?:'hidden'?>">
                            <td>流程结束时间</td>
                            <td><?=date("Y-m-d H:i:s",$model->finished_at); ?></td>
                            <td><?=isset($model->finishedUser)?$model->finishedUser->nick_name:'无'; ?></td>
                        </tr>
                        <tr class="<?= $model->factory_accepted_at?:'hidden'?>">
                            <td>工厂验收时间</td>
                            <td><?=date("Y-m-d H:i:s",$model->factory_accepted_at); ?></td>
                            <td><?=isset($model->factoryUser)?$model->factoryUser->nick_name:'无'; ?></td>
                        </tr>
                        <tr class="<?= $model->studio_end_at?:'hidden'?>">
                            <td>渲染完成时间</td>
                            <td><?=date("Y-m-d H:i:s",$model->studio_end_at); ?></td>
                            <td><?=isset($model->studioUser)?$model->studioUser->nick_name:'无'; ?></td>
                        </tr>
                        <tr class="<?= $model->studio_start_at?:'hidden'?>">
                            <td>开始渲染时间</td>
                            <td><?=date("Y-m-d H:i:s",$model->studio_start_at); ?></td>
                            <td><?=isset($model->studioUser)?$model->studioUser->nick_name:'无'; ?></td>
                        </tr>
                        <tr class="<?= $model->factory_end_at?:'hidden'?>">
                            <td>开始完成时间</td>
                            <td><?=date("Y-m-d H:i:s",$model->factory_end_at); ?></td>
                            <td><?=isset($model->factoryUser)?$model->factoryUser->nick_name:'无'; ?></td>
                        </tr>
                        <tr class="<?= $model->moulded_at?:'hidden'?>">
                            <td>压模时间</td>
                            <td><?=date("Y-m-d H:i:s",$model->moulded_at); ?></td>
                            <td><?=isset($model->factoryUser)?$model->factoryUser->nick_name:'无'; ?></td>
                        </tr>
                        <tr class="<?= $model->silver_at?:'hidden'?>">
                            <td>银版时间</td>
                            <td><?=date("Y-m-d H:i:s",$model->silver_at); ?></td>
                            <td><?=isset($model->factoryUser)?$model->factoryUser->nick_name:'无'; ?></td>
                        </tr>
                        <tr class="<?= $model->electroplate_at?:'hidden'?>">
                            <td>电绘时间</td>
                            <td><?=date("Y-m-d H:i:s",$model->electroplate_at); ?></td>
                            <td><?=isset($model->factoryUser)?$model->factoryUser->nick_name:'无'; ?></td>
                        </tr>
                        <tr class="<?= $model->factory_start_at?:'hidden'?>">
                            <td>开始开版时间</td>
                            <td><?=date("Y-m-d H:i:s",$model->factory_start_at); ?></td>
                            <td><?=isset($model->factoryUser)?$model->factoryUser->nick_name:'无'; ?></td>
                        </tr>
                        <tr class="<?= $model->approval_at?:'hidden'?>">
                            <td>确认时间</td>
                            <td><?=date("Y-m-d H:i:s",$model->approval_at); ?></td>
                            <td><?=isset($model->approvalUser)?$model->approvalUser->nick_name:'无'; ?></td>
                        </tr>
                        <tr>
                            <td>发起时间</td>
                            <td><?=date("Y-m-d H:i:s",$model->created_at); ?></td>
                            <td><?=isset($model->createUser)?$model->createUser->nick_name:'无'; ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
