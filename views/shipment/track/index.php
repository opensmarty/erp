<?php
use renk\yiipal\helpers\Html;
use yii\helpers\Url;
use renk\yiipal\grid\GridView;
use renk\yiipal\helpers\FileHelper;
use yii\bootstrap\ActiveForm;
use app\helpers\ItemStatus;
use app\helpers\CommonHelper;
/* @var $this yii\web\View */

?>
<div class="shipment-info">
    <div class="body-content">
        <div class="row">
            <div class="col-xs-12">
                <p style="padding-left: 10px;"><?=$shippingMethod?> <?=$trackNo?></p>
                <?=$data;?>
            </div>
        </div>

    </div>
</div>
