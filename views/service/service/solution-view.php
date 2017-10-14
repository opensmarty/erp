<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use app\helpers\Options;
use kartik\file\FileInput;
use renk\yiipal\helpers\ArrayHelper;
/* @var $this yii\web\View */

?>
<?php
$css = <<<CSS

.solution-content{
    /*margin-left: 100px;*/
    border: dashed 1px rgba(128, 128, 128, 0.46);
    padding: 10px;
    margin: 10px 0;
    word-wrap: break-word;
    word-break: break-all;
    overflow-y: auto;
}
CSS;
$this->registerCss($css);
?>

<div class="service-issue-index">

    <div class="body-content">
        <div class="row">
            <div class="col-xs-12">
            </div>
        </div>
        <div class="row">
            <div class="col-xs-9">
                <h3><?= $model->subject;?></h3>

                <div class="solution-content"><?=$model->content;?></div>

            </div>
            <div class="col-xs-3">
                <div id="issue_tags_tree"></div>
            </div>
        </div>
    </div>

</div>
<?php
$js = <<<JS

var selectedTags = [$model->tags];
    $(function () {
        $(window).resize(function () {
            var h = Math.max($(window).height() - 0, 420);
            $('#container, #data, #tree, #data .content').height(h).filter('.default').css('lineHeight', h + 'px');
        }).resize();

        $('#issue_tags_tree')
            .jstree({
                'core' : {
                    'data' : {
                        'url' : '/category/operation?operation=get_children&parent_id=70',
                        'data' : function (node) {
                            if(node.id=='#'){
                                //Magento 分类
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
            ;
    });

    //提交之前，获取Tags类目
    $("form button[name=submit-button]").click(function(){
        var ids = '';
        var checkedNodes = $('#issue_tags_tree').jstree(true).get_bottom_selected();
        ids = checkedNodes.join(",");
        $("form input#field_tags").val(ids);
    });
JS;

$this->registerJs($js);
?>
