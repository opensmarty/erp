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
use yii\bootstrap\ActiveForm;
use app\helpers\ItemStatus;
?>
<?php
$css = <<<CSS

CSS;
$this->registerCss($css);

?>
<div class="loan-index">
    <div class="body-content">
        <div class="row btn-group-top" style="overflow: hidden;">
            <div class="col-xs-12">
                <?php $form = ActiveForm::begin([
                    'options' => ['enctype' => 'multipart/form-data','id'=>'sales_analyse_form'],
                    'enableClientValidation' => false,
                    'enableAjaxValidation' => false,
                ]);?>
                <?php ActiveForm::end();?>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12 text-right">
                <span><?=$product->sku;?></span>
                <span><?=$img =\renk\yiipal\helpers\FileHelper::getThumbnailWithLink($product->getMasterImage(),$product->id);?></span>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
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
$sum = array_sum($data);
$average = round($sum/30,1);
$label = json_encode(array_keys($data));
$data = json_encode(array_values($data));

$js = <<<JS
var label=$label;
var data=$data;
var sum=$sum;
var average = $average;

var initChart = function(label,data){
    var loanChart = echarts.init(document.getElementById('analysis-chart'));
    var option = {
            title: {
                text: '过去30天总销量：'+sum+' (平均日销：'+average+')'
            },
            tooltip: {
                trigger: 'axis'
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
            series: [
                {
                    name:'销售历史',
                    type:'line',
                    stack: '总量',
                    data:data
                }
            ]
        };
    loanChart.setOption(option);
};

initChart(label,data);

JS;
$this->registerJs($js);
?>
