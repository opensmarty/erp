<?php
use renk\yiipal\helpers\Html;
use yii\helpers\Url;
use renk\yiipal\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\widgets\Pjax;
use app\helpers\Options;
/* @var $this yii\web\View */
?>
<?php
$css = <<<CSS
div.list-row{
    min-height: 400px;
}
span#filter_display_tags{
    display: inline-block;
    padding: 8px 0;
}
CSS;
$this->registerCss($css);
?>
<div class="product-index">
    <div class="body-content">
        <div class="row btn-group-top">
            <div class="col-xs-12">
                <div class="btn-group pull-right">
                    <div class="btn-group-top">
                        <?= Html::authLink('新增', ['solution-create'], ['class' => 'btn btn-success']) ?>
                        <?= Html::authSubmitButton('导出', ['export'], ['class' => 'btn btn-default download','form-class'=>'ajax-form download', 'data-action-before'=>'get_ids']) ?>
                    </div>
                </div>
            </div>
            <div class="col-xs-6">
            </div>
        </div>
        <div class="row list-row">
            <div class="col-xs-12">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'columns' => [

                        [
                            'class' => 'yii\grid\CheckboxColumn',
                            'checkboxOptions' => function ($model, $key, $index, $column) {
                                return ['value' => $model->id];
                            }
                        ],
                        [
                            'class' => 'renk\yiipal\grid\DataColumn',
                            'label' => '问题分类',
                            'attribute' => 'tags',
                            'filter'=>function($data){
                                $categories = \app\models\Category::find()->indexBy('id')->asArray()->all();
                                $gets = Yii::$app->request->get('ServiceIssueSolution',[]);
                                $tags = [];
                                if(isset($gets['tags'])){
                                    $tags = explode(",",$gets['tags']);
                                }
                                $label = '';
                                foreach($tags as $tag){
                                    if(empty($tag)||!isset($categories[$tag]))continue;
                                    $label .= $categories[$tag]['name'].",";
                                }
                                $label = rtrim($label,',');
                                $label = $label?:'全部';
                                return '<div class="" style="position: relative;"><span id="filter_display_tags">'.$label.' </span><a href="javascript:;" id="filter_tags_select"><span class="glyphicon glyphicon-pencil"></span></a><input type="hidden" name="ServiceIssueSolution[tags]" value="" id="filter_tags"/><div id="issue_tags_tree" style="position: absolute;top:42px;left:0;background: #FFF;border: solid 1px gray;"></div></div>';
                            },
                            'value' => function ($data) {
                                $categories = \app\models\Category::find()->indexBy('id')->asArray()->all();
                                $tags = explode(',',$data->tags);
                                $output = '';
                                foreach($tags as $tag){
                                    if(empty($tag)||!isset($categories[$tag]))continue;
                                    $output = $output.$categories[$tag]['name'].",";
                                }
                                return rtrim($output,',');
                            },
                            'format'=>'raw',
                            'enableSorting'=>false,
                        ],
                        'subject',
                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '登记人',
                            'attribute' => 'uid',
                            'value' => function ($data) {
                                return $data->user->nick_name;
                            },
                            'format'=>'raw',
                        ],
                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '更新时间',
                            'attribute' => 'updated_at',
                            'filter'=>false,
                            'value' => function ($data) {
                                return date("Y-m-d H:i:s",$data->updated_at);
                            },
                            'format'=>'raw',
                        ],
                        [
                            'class' => 'renk\yiipal\grid\ActionColumn',
                            'contentOptions'=>['style'=>'min-width: 75px;max-width: 75px;width: 75px;'],
                        ],
                    ],
                ]); ?>
            </div>
        </div>

    </div>
</div>
<?php
$js = <<<JS
var selectedTags = [$tags];

$(window).resize(function () {
    var h = Math.max($(window).height() - 0, 420);
    $('#container, #data, #tree, #data .content').height(h).filter('.default').css('lineHeight', h + 'px');
}).resize();
var initTags = function(){
    $('#issue_tags_tree')
    .jstree({
        'core' : {
            'data' : {
                'url' : '/category/operation?operation=get_children&parent_id=70',
                'data' : function (node) {
                    if(node.id=='#'){
                        //问题 分类
                        return { 'id' : 70 };
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
        $.each(nodes,function(){console.log(this);
            ids += this.id+",";
            tags += this.text+",";
        });
        $("#filter_tags").val(ids).change();
        //$("#filter_display_tags").text(tags);
    }).hide();
    ;
}
initTags();

$("#filter_tags_select").click(function(event){
    $("#issue_tags_tree").show();
    event.stopPropagation();

});
$("body").click(function(){
    $("#issue_tags_tree").hide();
});

JS;

$this->registerJs($js);
?>