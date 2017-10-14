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
div.form-group{
    /*display: inline-block;*/
    /*min-width: 32%;*/
    /*max-width: 96%;*/
}
CSS;
$this->registerCss($css);
?>

<div class="service-issue-index">
    <?php $form = ActiveForm::begin([
//        'options' => ['enctype' => 'multipart/form-data'],
        'enableClientValidation' => false,
        'enableAjaxValidation' => false,
    ]);?>
    <div class="body-content">
        <div class="row">
            <div class="col-xs-12">
            </div>
        </div>
        <div class="row">
            <div class="col-xs-9">
                <?= $form->field($model, 'subject')->textInput(['placeholder'=>'例如：关于产品掉石、掉色问题处置方案']);?>
                <div>
                <?= $form->field($model, 'content')->textarea(['id'=>'editor_solution']);?>
                </div>
            </div>
            <div class="col-xs-3">
                <?= $form->field($model, 'tags')->hiddenInput(['id'=>'field_tags']);?>
                <div id="issue_tags_tree"></div>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12 col-md-9">
                <div class="form-group">
                    <?= Html::submitButton('提交', ['class' => 'btn btn-primary', 'name' => 'submit-button']) ?>
                </div>
            </div>
        </div>
    </div>
    <?php ActiveForm::end();?>
</div>
<?php
$js = <<<JS

CKEDITOR.replace('editor_solution');
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
