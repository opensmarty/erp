<?php
use renk\yiipal\helpers\Html;
use yii\helpers\Url;
use renk\yiipal\grid\GridView;
use renk\yiipal\helpers\FileHelper;
use app\helpers\ItemStatus;
/* @var $this yii\web\View */
?>
<?php
$css = <<<CSS
form .btn-group .form-daterange{
    float: left;
    width: 200px;
    margin-right: 8px;
}
CSS;
$this->registerCss($css);
?>
<div class="order-index">
    <div class="body-content">

        <div class="row btn-group-top">
            <div class="col-xs-6">
                <form action="index" method="post" id="produce_report_form">
                <div class="btn-group">
                    <div class="btn-group-top">
                        <input type="text" class="form-control form-daterange" name="date_range" id="date_range" value="<?= date('Y-m-d').'/'.date('Y-m-d'); ?>"/>
                        <?= Html::submitButton('查询',['class'=>'btn btn-primary','id'=>'produce_report_btn'])?>
                    </div>
                </div>
                </form>
            </div>
            <div class="col-xs-6">
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <div>
                    <label>定制款下单：</label><span id="custom_qty"></span>
                    <label>定制款验收：</label><span id="custom_qty_passed"></span>
                    <label>定制款次品：</label><span id="custom_qty_rejects"></span>
                    <label>定制款次品已解决：</label><span id="custom_qty_solved"></span>
                </div>
                <div>
                    <label>库存款下单：</label><span id="stock_qty"></span>
                    <label>库存款验收：</label><span id="stock_qty_passed"></span>
                    <label>库存款次品：</label><span id="stock_qty_rejects"></span>
                    <label>库存款次品已解决：</label><span id="stock_qty_solved"></span>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$js = <<<JS
$(document).on('submit','form#produce_report_form',function(){
    var options = {
        target: '',
        success: function(reponse,status, event,target){
            if (reponse.status === '00') {
                var data = reponse.data;
                $("#custom_qty").text(data.custom_qty);
                $("#custom_qty_passed").text(data.custom_qty_passed);
                $("#custom_qty_rejects").text(data.custom_qty_rejects);
                $("#custom_qty_solved").text(data.custom_qty_solved);
                $("#stock_qty").text(data.stock_qty);
                $("#stock_qty_passed").text(data.stock_qty_passed);
                $("#stock_qty_rejects").text(data.stock_qty_rejects);
                $("#stock_qty_solved").text(data.stock_qty_solved);
            } else {
                bootbox.alert(reponse.msg);
            }
            return false;
        }
    };
    $(this).ajaxSubmit(options);
    return false;
});
$("#produce_report_btn").click();
JS;

$this->registerJs($js);
?>
