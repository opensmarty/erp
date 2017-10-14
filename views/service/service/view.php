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
.issue-label{
    font-weight: bold;
    width: 100px;
    text-align: left;
    display: inline-block;
}
div.issue-list-item{
    border: dashed 1px rgba(128, 128, 128, 0.46);
    padding: 10px;
    margin: 4px 0;
}
.issue-body{
    word-wrap: break-word;
    word-break: break-all;
    overflow-y: auto;
    max-height: 100px;
}
.issue-description,.issue-solution{
    /*margin-left: 100px;*/
    border: dashed 1px rgba(128, 128, 128, 0.46);
    padding: 10px;
    margin: 10px 0;
    word-wrap: break-word;
    word-break: break-all;
    overflow-y: auto;
    max-height: 100px;
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
                <div><span class="issue-label">订单编号：</span><?=$model->ext_order_id;?></div>
                <div><span class="issue-label">出处：</span><?=Options::serviceIssueFromOptions($model->from);?></div>
                <div><span class="issue-label">状态：</span><?=Options::issueStatusOptions($model->status);?></div>
                <div><span class="issue-label">登记人：</span><?=$model->reportUser->nick_name;?></div>
                <div><span class="issue-label">解决人：</span><?=isset($model->solvedUser->nick_name)?$model->reportUser->nick_name:'未解决';?></div>
                <div><span class="issue-label">客户姓名：</span><?=$model->customer_name;?></div>
                <div><span class="issue-label">客户邮件：</span><?=$model->customer_email;?></div>
                <div><span class="issue-label">客户电话：</span><?=$model->customer_tel;?></div>

                <div><span class="issue-label">问题描述：</span>
                    <div class="issue-description"><?=$model->description;?></div>
                </div>
            </div>
            <div class="col-xs-3">
                <div id="issue_tags_tree"></div>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-9">
                <div style="padding: 10px;margin: 10px 0; border-top: solid 2px #00a65a;">问题处理记录:</div>
                <?php foreach($items as $item):?>
                    <div class="issue-list-item">
                        <div class="issue-subject"><h4><?= $item->subject?></h4></div>
                        <div class="issue-body"><?=$item->content?></div>
                        <div class="issue-footer text-right"><span class="issue-reporter"><?=$item->user->nick_name;?></span> - <span class="issue-date"><?= date("Y-m-d H:i:s",$item->created_at);?></span></div>
                    </div>
                <?php endforeach;?>
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
