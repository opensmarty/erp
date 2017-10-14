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
                        <th style="width: 45px;">#</th>
                        <th style="width: 65px;">订单编号</th>
                        <th style="width: 100px;">订单状态</th>
                        <th style="width: 65px;">时间</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach($logs as $index=> $log): ?>
                        <tr>
                            <td><?=count($logs)-$index;?></td>
                            <td><?= $log->ext_order_id;?></td>
                            <td><?= ItemStatus::paymentStatusOptions($log->payment_status);?></td>
                            <td><?= date('Y-m-d H:i:s',$log->created_at)?></td>
                        </tr>
                    <?php endforeach;?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
