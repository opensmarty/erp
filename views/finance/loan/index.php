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
            <div class="col-xs-6">
                <form action="index" method="post" id="loan_produce_form">
                    <div class="btn-group">
                        <div class="btn-group-top">
                            <input type="text" class="form-control form-daterange" name="date_range" id="date_range" value="<?= date('Y-m-d',strtotime('-1 month')).'/'.date('Y-m-d'); ?>"/>
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
            <div class="col-xs-12">
                <div id="loan-chart" style="width: 100%;height:400px;"></div>
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
    var loanChart = echarts.init(document.getElementById('loan-chart'));
    option = {
        title: {
            text: '货贷统计',
            subtext: '定制生产、库存生产和总计的货款'
        },
        tooltip: {
            trigger: 'axis',
            axisPointer: {
                type: 'shadow'
            }
        },
        toolbox: {
            feature: {
                saveAsImage: {}
            }
        },
        legend: {
            data: ['总计','库存生产','定制生产']
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
            data: ['货款']
        },
        series: [
            {
                name: '总计',
                type: 'bar',
                data: [data.total_cost],
                label: {
                    normal: {
                        show: true,
                        position: 'inside'
                    }
                }
            },
            {
                name: '库存生产',
                type: 'bar',
                data: [data.stock_cost],
                label: {
                    normal: {
                        show: true,
                        position: 'inside'
                    }
                }
            },
            {
                name: '定制生产',
                type: 'bar',
                data: [data.custom_cost],
                label: {
                    normal: {
                        show: true,
                        position: 'inside'
                    }
                }
            }
        ]
    };
    loanChart.setOption(option);
};

JS;
$this->registerJs($js);
?>
