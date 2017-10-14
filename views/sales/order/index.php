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
use app\helpers\ItemStatus;
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
<div class="loan-index">
    <div class="body-content">
        <div class="row btn-group-top">
            <form action="index" method="post" id="order_analyse_form">
                <div class="col-xs-10">
                    <table class="table table-bordered" id="compare_filter_table">
                        <tr>
                            <td colspan="5" class="text-left"><?=Html::radioList('analyse_type','total',['total'=>'总量分析','tendency'=>'趋势分析'],['class'=>'analyse-type'])?></td>
                            <td colspan="7" class="text-right"><?=Html::radioList('view_type','day',['day'=>'日视图','week'=>'周视图','month'=>'月视图','year'=>'年视图'])?></td>
                        </tr>
                        <tr>
                            <th>条件</th>
                            <th>分站</th>
                            <th>国家</th>
                            <th>客户端</th>
                            <th>支付状态</th>
                            <th>订单类型</th>
                            <th>物流方式</th>
                            <th>退换货</th>
                            <th>订单状态</th>
                            <th>优惠券</th>
                            <th>创建日期</th>
                            <th></th>
                        </tr>
                        <tr id="base_compare_row">
                            <td>1</td>
                            <td><?=Html::dropDownList('website[]',null,Options::websiteOptions(false,'全部'),['id'=>'website'])?></td>
                            <td><?=Html::dropDownList('country[]',null,Options::countryOptions(false,'全部'),['id'=>'country'])?></td>
                            <td><?=Html::dropDownList('client[]',null,Options::clients(false,'全部'),['id'=>'client'])?></td>
                            <td><?=Html::dropDownList('payment_status[]',null,Options::paymentStatusOptions(false,'全部'),['id'=>'payment_status'])?></td>
                            <td><?=Html::dropDownList('order_type[]',null,Options::orderTypeOptions(false,'全部'),['id'=>'order_type'])?></td>
                            <td><?=Html::dropDownList('shipping_method[]',null,Options::shippingMethods(false,'全部'),['id'=>'shipping_method'])?></td>
                            <td><?=Html::dropDownList('refund_exchange[]',null,Options::yesNoOptions(false,'全部'),['id'=>'refund_exchange'])?></td>
                            <td><?=Html::dropDownList('order_status[]',null,ItemStatus::allStatus(false,'全部'),['id'=>'order_status'])?></td>
                            <td><?=Html::input('text','coupon_code[]',"",['class'=>'','id'=>'coupon_code'])?></td>
                            <td><?=Html::input('text','created_at[]',"",['class'=>'form-daterange','id'=>'created_at'])?></td>
                            <td><button class="btn btn-xs hidden remove-btn"><span class="glyphicon glyphicon-remove text-danger"></span></button></td>
                        </tr>
                    </table>
                </div>
                <div class="col-xs-2">
                    <div>
                        <button class="btn btn-md btn-default" id="add_compare_row">添加对比</button>
                        <?= Html::submitButton('开始分析',['class'=>'btn btn-primary','id'=>'produce_report_btn'])?>
                    </div>
                </div>
            </form>
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
            <div class="col-xs-12">
                <div id="analysis-chart" style="width: 100%;height:400px;"></div>
            </div>
        </div>
    </div>
</div>
<?php
$js = <<<JS
$(document).on('submit','form#order_analyse_form',function(){
    var options = {
        target: '',
        success: function(reponse,status, event,target){
            if (reponse.status === '00') {
                var data = reponse.data;
                initChart(data.label,data.legend,data.data);
            } else {
                bootbox.alert(reponse.msg);
            }
            return false;
        }
    };
    $(this).ajaxSubmit(options);
    return false;
});

var rebuildAnalyseType = function(){
    var chartType = $("input[name=analyse_type]:checked").val();
    if(chartType == 'total'){
        $(".form-daterange").removeAttr("disabled");
    }else{
        $(".form-daterange").attr("disabled","disabled");
        $("#base_compare_row .form-daterange").removeAttr("disabled");
    }
};

$(document).on("click",".analyse-type input",function(){
    rebuildAnalyseType();
});

$(document).on("click",'#add_compare_row',function(){
    var len = $("#compare_filter_table tr").length;
    if(len>6){
        bootbox.alert("最多可对比5组数据");
        return false;
    }
    $("#base_compare_row").clone().removeAttr("id").hide().find("select").val(0).end().find(".remove-btn").removeClass("hidden").end().appendTo("#compare_filter_table").animate({opacity: 'show'},'slow');
    $("#compare_filter_table tr").each(function(index, item){
        if(index>0){
            $(item).find("td:first").text(index-1);
        }
    });
    init_date_range_input();
    rebuildAnalyseType();
    return false;
});

$(document).on("click",'#compare_filter_table .remove-btn',function(){
    $(this).parent().parent().animate({opacity: 'hide'},'slow',function(){
        $(this).remove();
        $("#compare_filter_table tr").each(function(index, item){
            if(index>0){
                $(item).find("td:first").text(index-1);
            }
        });
    });
    return false;
});

//$("#produce_report_btn").click();

var initChart = function(label,legend,data){
    var loanChart = echarts.init(document.getElementById('analysis-chart'));
    var chartType = $("input[name=analyse_type]:checked").val();
    if(chartType=='total'){
        option = {
            title: {
                text: '订单分析'
            },
            tooltip: {
                trigger: 'axis',
                axisPointer: {
                    type: 'shadow'
                }
            },
            grid: {
                left: '3%',
                right: '4%',
                bottom: '3%',
                containLabel: true
            },
            xAxis: {
                type: 'value',
                boundaryGap: [0, 0.01]
            },
            yAxis: {
                type: 'category',
                data: label
            },
            series: data
        };
    }else{
        option = {
            title: {
                text: '订单分析'
            },
            tooltip: {
                trigger: 'axis'
            },
            legend: {
                data:legend
            },
            grid: {
                left: '3%',
                right: '4%',
                bottom: '3%',
                containLabel: true
            },
            toolbox: {
                feature: {
                    saveAsImage: {}
                }
            },
            xAxis: {
                type: 'category',
                boundaryGap: false,
                data: label
            },
            yAxis: {
                type: 'value'
            },
            series: data
        };
    }


    loanChart.setOption(option);
};
//initChart();
JS;
$this->registerJs($js);
?>
