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
form .btn-group .form-daterange{
    float: left;
    width: 200px;
    margin-right: 8px;
}
.magento-cid{
    position: relative;
    max-width: 200px;
}

.magento_tags_tree{
    position: fixed;
    top: 175px;
    left: 240px;
    /* right: 0px; */
    z-index: 999;
    background: #FFFFFF;
}
CSS;
$this->registerCss($css);

?>
<div class="loan-index">
    <div class="body-content">
        <div class="row btn-group-top" style="overflow: hidden;">
            <?php $form = ActiveForm::begin([
                'options' => ['enctype' => 'multipart/form-data','id'=>'sales_analyse_form'],
                'enableClientValidation' => false,
                'enableAjaxValidation' => false,
            ]);?>
                <div class="col-xs-11">
                    <table class="table table-bordered" id="compare_filter_table">
                        <tr>
                            <td colspan="5" class="text-left"><?=Html::radioList('analyse_type','total',['total'=>'总量分析','tendency'=>'趋势分析'],['class'=>'analyse-type'])?></td>
                            <td colspan="7" class="text-right"><?=Html::radioList('view_type','day',['day'=>'日视图','week'=>'周视图','month'=>'月视图','year'=>'年视图'])?></td>
                        </tr>
                        <tr>
                            <th>条件</th>
                            <th>网站</th>
                            <th>类目</th>
                            <th>SKU</th>
                            <th>主钻类型</th>
                            <th>主钻颜色</th>
                            <th>电镀颜色</th>
                            <th>成本区间</th>
                            <th class="hidden">价格区间</th>
                            <th>选款人</th>
                            <th>来源</th>
                            <th>销售日期</th>
                            <th><div class="magento_tags_tree"></div></th>
                        </tr>
                        <tr id="base_compare_row">
                            <td class="index">1</td>
                            <td><?=Html::dropDownList('website[]',null,Options::websiteOptions(false,'全部'),['id'=>'website','class'=>'form-control w50'])?></td>
                            <td>
                                <div class="magento-cid">
                                    <span class="filter_display_tags"></span>
                                    <a href="javascript:;" id="filter_tags_select" class="filter_tags_select"><span class="glyphicon glyphicon-pencil"></span></a>
                                    <input type="hidden" name="magento_cid[]" value="" id="filter_tags" class="filter_tags"/>
                                </div>
                            </td>
                            <td><?=Html::textInput('sku[]','',['class'=>'form-control w100'])?></td>
                            <td><?=Html::dropDownList('stone_type[]',null,Options::stoneType(false,'全部'),['class'=>'form-control w150'])?></td>
                            <td><?=Html::dropDownList('stone_color[]',null,Options::stoneColor(false,'全部'),['id'=>'stoneColor','class'=>'form-control w200'])?></td>
                            <td><?=Html::dropDownList('electroplating_color[]',null,Options::electroplatingColor(false,'全部'),['id'=>'electroplating_color','class'=>'form-control'])?></td>
                            <td><?=Html::input('number','cost_price_start[]','',['class'=>'form-control w40 pd2 fl'])?><span class="fl" style="line-height: 32px;">-</span><?=Html::input('number','cost_price_end[]','',['class'=>'form-control w40 pd2'])?></td>
                            <td class="hidden"><?=Html::input('number','sales_price_start[]','',['class'=>'form-control w40 pd2 fl'])?><span class="fl"  style="line-height: 32px;">-</span><?=Html::input('number','sales_price_end[]','',['class'=>'form-control w40 pd2'])?></td>
                            <td><?=Html::input('text','chosen_uid[]','',['class'=>'form-control w100'])?></td>
                            <td><?=Html::input('text','source[]','',['class'=>'form-control w100'])?></td>
                            <td><?=Html::input('text','created_at[]',"",['class'=>'form-control form-daterange w200','id'=>'created_at'])?></td>
                            <td><button class="btn btn-xs hidden remove-btn"><span class="glyphicon glyphicon-remove text-danger"></span></button></td>
                        </tr>
                    </table>
                </div>
                <div class="col-xs-1">
                    <div>
                        <button class="btn btn-md btn-default" id="add_compare_row">添加对比</button>
                        <?= Html::submitButton('开始分析',['class'=>'btn btn-primary','id'=>'produce_report_btn'])?>
                    </div>
                </div>
            <?php ActiveForm::end();?>
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
$(document).on('submit','form#sales_analyse_form',function(){
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
    if(len>11){
        bootbox.alert("最多可对比10组数据");
        return false;
    }
    $("#base_compare_row").clone().removeAttr("id").hide().find("select").val(0).end().find("input").val("").end().find(".filter_display_tags").text("").end().find(".remove-btn").removeClass("hidden").end().appendTo("#compare_filter_table").animate({opacity: 'show'},'slow');
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
        var colors = ['#5793f3', '#d14a61', '#675bba'];
        option = {
            color: colors,
            tooltip: {
                trigger: 'axis'
            },
            grid: {
                right: '20%'
            },
            toolbox: {
                feature: {
                    saveAsImage: {show: true}
                }
            },
            legend: {
                data:['销售数量','销售金额','转化率']
            },
            xAxis: [
                {
                    type: 'category',
                    axisTick: {
                        alignWithLabel: true
                    },
                    data: label
                }
            ],
            yAxis: [
                {
                    type: 'value',
                    name: '销售数量',
                    position: 'right',
                    axisLine: {
                        lineStyle: {
                            color: colors[0]
                        }
                    },
                    axisLabel: {
                        formatter: '{value} 个'
                    }
                },
                {
                    type: 'value',
                    name: '销售金额',
                    position: 'right',
                    offset: 80,
                    axisLine: {
                        lineStyle: {
                            color: colors[1]
                        }
                    },
                    axisLabel: {
                        formatter: '{value} 美元'
                    }
                },
                {
                    type: 'value',
                    name: '转化率',
                    position: 'left',
                    axisLine: {
                        lineStyle: {
                            color: colors[2]
                        }
                    },
                    axisLabel: {
                        formatter: '{value} %'
                    }
                }
            ],
            series: data
        };
    }else{
        option = {
            title: {
                text: '销售分析'
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



var currentTagId = 1;
var selectedTags = [];

$(window).resize(function () {
    var h = Math.max($(window).height() - 0, 420);
    $('#container, #data, #tree, #data .content').height(h).filter('.default').css('lineHeight', h + 'px');
}).resize();
var initTags = function(){
    $('.magento_tags_tree')
    .jstree({
        'core' : {
            'data' : {
                'url' : '/category/operation?operation=get_children&parent_id=6',
                'data' : function (node) {
                    if(node.id=='#'){
                        //问题 分类
                        return { 'id' : 6 };
                    }else{
                        return { 'id' : node.id};
                    }

                }
            },
            'check_callback' : true,
            'themes': {
                'name': 'default',
                'responsive': true
            }
        },
        'force_text' : true,
        'plugins' : ['checkbox','state','dnd']
    })
    .on('state_ready.jstree', function (e, data) {
        data.instance.uncheck_all();
        data.instance.check_node(selectedTags);

    })
    .on('activate_node.jstree',function(e,data){
        var nodes = data.instance.get_bottom_selected(true);
        var tags = '';
        var ids = '';
        $.each(nodes,function(){
            ids += this.id+",";
            tags += this.text+",";
        });
        $(".filter_tags").eq(currentTagId-1).val(ids);
        $(".filter_display_tags").eq(currentTagId-1).text(tags);
    }).hide();
    ;
}
initTags();
$(document).on("click",".filter_tags_select",function(event){
    $(".magento_tags_tree").hide();
    currentTagId = $(this).parents("tr").find("td.index").text();
    var tree = $(".magento_tags_tree");
    var offset = (175+50*currentTagId);
    tree.css("top",offset+"px");
    tree.show();
    var ids = $(".filter_tags").eq(currentTagId-1).val();
    tree.jstree("deselect_all");
    tree.jstree("select_node",ids.split(","));
    event.stopPropagation();
});

$(document).on("click","body",function(){
    $(".magento_tags_tree").hide();
});
JS;
$this->registerJs($js);
?>
