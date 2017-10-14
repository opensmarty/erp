<?php
/**
 * index.php
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/7/15
 */
use renk\yiipal\helpers\Html;
use app\helpers\Options;
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
<?php
$paymentMethods = Options::paymentMethods();
array_unshift($paymentMethods,'全部');
$clients = Options::clients();
array_unshift($clients,'全部');

$websites = Options::websiteOptions();
array_unshift($websites,'全部');
?>
<div class="loan-index">
    <div class="body-content">
        <div class="row btn-group-top">
            <div class="col-xs-6">
                <form action="index" method="post" id="loan_produce_form">
                    <div class="btn-group">
                        <div class="btn-group-top">
                            <input type="text" class="form-control form-daterange" name="date_range" id="date_range" value="<?= date('Y-m-d',strtotime('-1 month')).'/'.date('Y-m-d'); ?>"/>
                            <?= Html::dropDownList('payment_method','',$paymentMethods,['class'=>'form-control w100 fl mr8'])?>
                            <?= Html::dropDownList('client','',$clients,['class'=>'form-control w100 fl mr8'])?>
                            <?= Html::dropDownList('source','',$websites,['class'=>'form-control w100 fl mr8'])?>
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
                <div class="btn-group pull-right">
                    <div class="btn-group-top">
                        <?= Html::authLink('新增', ['create'], ['class' => 'btn btn-success']) ?>
                        <?= Html::authSubmitButton('导出', ['export'], ['class' => 'btn btn-default download', 'form-class' => 'ajax-form download', 'data-action-before' => 'get_ids']) ?>
                    </div>
                </div>
            </div>
            <div class="col-xs-6">
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12 col-md-6 ">
                <h4>营收合计:</h4>
                <ul class="list-group" id="revenue_list">
                    <li class="list-group-item">查询中...</li>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php
$js = <<<JS
$(document).on('submit','form#loan_produce_form',function(){
    var options = {
        target: '',
        success: function(reponse,status, event,target){
            if (reponse.status === '00') {
                var data = reponse.data;
                initChart(data);
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

var initChart = function(data){
    var html = '';
    $.each(data,function(index,row){
        html += '<li class="list-group-item"><label class="label label-success">'+index+'</label> - '+row.total+'</li>';
    });
    if(html == ''){
        html = '<li class="list-group-item">没有数据记录</li>';
    }
    $("#revenue_list").html(html);
};

JS;
$this->registerJs($js);
?>
