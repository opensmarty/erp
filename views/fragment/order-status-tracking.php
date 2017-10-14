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
                        <th style="width: 45px;">编号</th>
                        <th style="max-width: 120px;">订单状态</th>
                        <th style="width: 240px;">状态描述</th>
                        <th style="width: 85px;">处理时间</th>
                        <th style="width: 75px;">执行人</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach($logs as $index=> $log): ?>
                        <tr>
                            <td><?=count($logs)-$index;?></td>
                            <td><?= ItemStatus::allStatus($log->status);?></td>
                            <td><?= $log->description;?></td>
                            <td><?= date('Y-m-d H:i:s',$log->created_at)?></td>
                            <td><?=$log->user->nick_name;?></td>
                        </tr>
                    <?php endforeach;?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
