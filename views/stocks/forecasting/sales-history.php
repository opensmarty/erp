<?php
use renk\yiipal\helpers\Html;
use yii\helpers\Url;
use renk\yiipal\grid\GridView;
use yii\bootstrap\ActiveForm;
/* @var $this yii\web\View */
?>
<?php
$css = <<<CSS
.morris-hover{position:absolute;z-index:1090;}.morris-hover.morris-default-style{border-radius:10px;padding:6px;color:#f9f9f9;background:rgba(0, 0, 0, 0.8);border:solid 2px rgba(0, 0, 0, 0.9);font-weight: 600;font-size:14px;text-align:center;}.morris-hover.morris-default-style .morris-hover-row-label{font-weight:bold;margin:0.25em 0;}
.morris-hover.morris-default-style .morris-hover-point{white-space:nowrap;margin:0.1em 0;}
CSS;
$this->registerCss($css);
?>
<div class="sales-history">
    <div class="body-content">
        <div class="row btn-group-top">
            <div class="col-xs-12">
                <div class="btn-group pull-right">
                    <div class="btn-group-top">
                    </div>
                </div>
            </div>
            <div class="col-xs-6">
            </div>
        </div>
        <div class="row">
            <div class="col-xs-6">
                <span>产品：<?= $forecasting->sku?> 尺码：<?=$forecasting->size?> 款式：<?=\app\helpers\Options::ringTypes($forecasting->size_type);?></span>
            </div>
            <div class="col-xs-4 pull-right">
                <div class="text-right"> 过去<?= $dateRange;?>天总销量：<?= $totalQty;?> &nbsp;</div>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <div id="sales_history" class="box box-solid bg-teal-gradient"></div>
            </div>
        </div>

    </div>
</div>
<?php
$js = <<<JS
    var data = $data;
    var line = new Morris.Line({
        element: 'sales_history',
        resize: true,
        data: data,
        xkey: 'short_date',
        ykeys: ['qty'],
        labels: ['销量'],
        lineColors: ['#efefef'],
        lineWidth: 2,
        hideHover: 'auto',
        gridTextColor: "#fff",
        gridStrokeWidth: 0.4,
        pointSize: 4,
        pointStrokeColors: ["#efefef"],
        gridLineColor: "#efefef",
        gridTextFamily: "Open Sans",
        gridTextSize: 10
    });
JS;

$this->registerJs($js);
?>