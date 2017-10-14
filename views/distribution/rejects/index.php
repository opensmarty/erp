<?php
use renk\yiipal\helpers\Html;
use yii\helpers\Url;
use renk\yiipal\grid\GridView;
use app\helpers\CommonHelper;
use renk\yiipal\helpers\FileHelper;
use app\helpers\ItemStatus;
use app\helpers\Options;
/* @var $this yii\web\View */
?>
<div class="order-index">
    <div class="body-content">
        <div class="row btn-group-top">
            <div class="col-xs-12">
                <div class="btn-group pull-right">
                    <div class="btn-group-top">
                        <?= Html::authSubmitButton('导出次品', ['export'], ['class' => 'btn btn-success','form-class'=>'ajax-form download', 'data-action-before'=>'get_ids']) ?>
                    </div>
                </div>
            </div>
            <div class="col-xs-6">
            </div>
        </div>
        <div class="row">
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
                            'class' => 'yii\grid\DataColumn',
                            'label' => '订单编号',
                            'attribute' => 'ext_order_id',
                            'value' => function ($data) {
                                if($data->item_type == 'stockup'){
                                    $ext_order_id = 'S-'.$data->stockOrder->ext_order_id;
                                }else{
                                    $ext_order_id = $data->customOrder->ext_order_id;
                                }
                                return '<span class="item">'.$ext_order_id.'</span>';;
                            },
                            'format' => 'raw',
                        ],
                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '订单类型',
                            'contentOptions'=>['style'=>'max-width: 75px;'],
                            'filter'=>Options::orderTypes(),
                            'attribute' => 'item_type',
                            'value' => function ($data) {
                                return '<span class="item">'.Options::orderTypes($data->item_type).'</span>';
                            },
                            'format' => 'raw',
                        ],
                        [
                            'class' => 'yii\grid\DataColumn',
                            'contentOptions'=>['style'=>'max-width: 80px;padding:0px;'],
                            'label' => '图片',
                            'attribute' => 'file',
                            'value' => function ($data) {
                                $image = $data->product->getMasterImage();
                                return \renk\yiipal\helpers\FileHelper::getThumbnailWithLink($image,$data->id);
                            },
                            'format'=>'raw',
                            'enableSorting'=>false,
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '产品编号',
                            'attribute' => 'product_id',
                            'value' => function ($data) {
                                $output = '';
                                $output.='<span class="item">'.$data->product_id.'</span>';
                                return $output;
                            },
                            'format' => 'raw',
                        ],
                        
                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => 'SKU',
                            'attribute' => 'sku',
                            'value' => function ($data) {
                                $output = '';
                                $output.='<span class="item">'.$data->sku.'</span>';
                                return $output;
                            },
                            'format' => 'raw',
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '尺码|刻字',
                            'attribute' => 'product_options',
                            'contentOptions'=>['style'=>'min-width: 170px;max-width: 170px;'],
                            'value' => function ($data) {
                                $output = '<span class="item">';
                                if(!empty($data->engravings)){
                                    $output .= '刻字内容：'.$data->engravings.'<br/>';
                                }
                                $output.= '美国码：'.$data->size_us;
                                if($data->product_type != 'none'){
                                    $output.='['.\app\helpers\Options::ringTypes($data->product_type).']';
                                }
                                $output .='</span>';
                                return $output;
                            },
                            'format' => 'raw',
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '次品总数',
                            'attribute' => 'qty_rejected',
                            'value' => function ($data) {
                                $output = '';
                                $output.='<span class="item">'.$data->qty_rejected.'</span>';
                                return $output;
                            },
                            'format' => 'raw',
                        ],

                        [
                            'class' => 'renk\yiipal\grid\DataColumn',
                            'label' => '次品原因',
                            'attribute' => 'tags',
                            'filter'=>function($data){
                                $categories = \app\models\Category::find()->indexBy('id')->asArray()->all();
                                $gets = Yii::$app->request->get('StockOrderRejected',[]);
                                $tags = [];
                                if(isset($gets['reject_tags'])){
                                    $tags = explode(",",$gets['reject_tags']);
                                }
                                $label = '';
                                foreach($tags as $tag){
                                    if(empty($tag)||!isset($categories[$tag]))continue;
                                    $label .= $categories[$tag]['name'].",";
                                }
                                $label = rtrim($label,',');
                                $label = $label?:'全部';
                                return '<div class="" style="position: relative;"><span id="filter_display_tags">'.$label.' </span><a href="javascript:;" id="filter_tags_select"><span class="glyphicon glyphicon-pencil"></span></a><input type="hidden" name="StockOrderRejected[reject_tags]" value="" id="filter_tags"/><div id="reject_tags_tree" style="position: absolute;top:42px;left:0;background: #FFF;border: solid 1px gray;"></div></div>';
                            },
                            'value' => function ($data) {
                                $tags = explode(',',$data->reject_tags);
                                $output = '';
                                foreach($tags as $tag){
                                    if(empty($tag)||!isset($data->categories[$tag]))continue;
                                    $output = $output.$data->categories[$tag]['name'].",";
                                }
                                return rtrim($output,',');
                            },
                            'format'=>'raw',
                            'enableSorting'=>false,
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '修复总数',
                            'attribute' => 'qty_solved',
                            'value' => function ($data) {
                                $output = '';
                                $output.='<span class="item">'.$data->qty_solved.'</span>';
                                return $output;
                            },
                            'format' => 'raw',
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '状态',
                            'attribute' => 'item_status',
                            'filter'=>Options::rejectsStatus(),
                            'value' => function ($data) {
                                $fontColor = '';
                                if($data->item_status == 'solved'){
                                    $fontColor = 'text-success';
                                }
                                $output = '';
                                $output.='<span class="item '.$fontColor.'">'.Options::rejectsStatus($data->item_status).'</span>';
                                return $output;
                            },
                            'format' => 'raw',
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '报告人',
                            'attribute' => 'report_uid',
                            'value' => function ($data) {
                                if($data->reportUser){
                                    return $data->reportUser->nick_name;
                                }else{
                                    return null;
                                }
                            },
                            'format' => 'raw',
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '解决人',
                            'attribute' => 'solved_uid',
                            'value' => function ($data) {
                                if($data->solvedUser){
                                    return $data->solvedUser->nick_name;
                                }else{
                                    return null;
                                }
                            },
                            'format' => 'raw',
                        ],

                        [
                            'class' => 'renk\yiipal\grid\DataColumn',
                            'label' => '报告时间',
                            'contentOptions'=>['style'=>'width: 110px;'],
                            'filter'=>function($model){
                                $gets = Yii::$app->request->get('StockOrderRejected',[]);
                                $created_at = isset($gets['created_at'])?$gets['created_at']:'';
                                $output = '<input type="text" class="form-control form-daterange pull-right" name="StockOrderRejected[created_at]" value="'.$created_at.'"/>';
                                return $output;
                            },
                            'attribute' => 'created_at',
                            'value' => function ($data) {
                                return '<span class="item">'.date("Y-m-d H:i:s",$data->created_at).'</span>';
                            },
                            'format' => 'raw',
                        ],
                        [
                            'class' => 'renk\yiipal\grid\DataColumn',
                            'label' => '解决时间',
                            'contentOptions'=>['style'=>'width: 110px;'],
                            'filter'=>function($model){
                                $gets = Yii::$app->request->get('StockOrderRejected',[]);
                                $solved_at = isset($gets['solved_at'])?$gets['solved_at']:'';
                                $output = '<input type="text" class="form-control form-daterange pull-right" name="StockOrderRejected[solved_at]" value="'.$solved_at.'"/>';
                                return $output;
                            },
                            'attribute' => 'solved_at',
                            'value' => function ($data) {
                                if(empty($data->solved_at)){
                                    return null;
                                }
                                return '<span class="item">'.date("Y-m-d H:i:s",$data->solved_at).'</span>';
                            },
                            'format' => 'raw',
                        ],
                        [
                            'class' => 'renk\yiipal\grid\ActionColumn',
                            'contentOptions'=>['style'=>'min-width: 75px;'],
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
    $('#reject_tags_tree')
    .jstree({
        'core' : {
            'data' : {
                'url' : '/category/operation?operation=get_children&parent_id=96',
                'data' : function (node) {
                    if(node.id=='#'){
                        //问题 分类
                        return { 'id' : 96 };
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
    $("#reject_tags_tree").show();
    event.stopPropagation();

});
$("body").click(function(){
    $("#reject_tags_tree").hide();
});

JS;

$this->registerJs($js);
?>