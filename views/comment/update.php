<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
?>
<?php
$css = <<<CSS
#treeview-checkable .list-group{
    max-height: 100px;
}
CSS;
$this->registerCss($css);
?>
<div class="comment-update content">
    <?php $form = ActiveForm::begin([
        'options' => ['id'=>'comment-form','enctype' => 'multipart/form-data','class'=>'ajax-form'],
        'enableClientValidation' => false,
        'enableAjaxValidation' => false,
    ]);?>
    <div class="body-content">
        <div class="row">
            <div class="col-lg-12">
                <div id="treeview-checkable" class=""></div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12 col-md-12">
                <?= Html::hiddenInput('Comment[visible_uids]','',['id'=>'visible_uids']);?>
            </div>
            <div class="col-lg-12 col-md-12">
                <?= $form->field($model, 'content')->label('备注内容')->textarea(['id'=>'editor','class'=>'required']); ?>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12 col-dm-12">
                <div class="form-group pull-right">
                    <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                    <?= Html::submitButton('提交', ['class' => 'btn btn-primary  keep-modal', 'name' => 'submit-button']) ?>
                </div>
            </div>
        </div>
    </div>
    <?php ActiveForm::end();?>
</div>
<?php
$js = <<<JS
CKEDITOR.replace('editor');
var defaultData = $userTree;

var checkableTree = $('#treeview-checkable').treeview({
  data: defaultData,
  showIcon: false,
  showCheckbox: true,
  onNodeChecked: function(event, node) {
    $('#checkable-output').prepend('<p>' + node.text + ' was checked</p>');
  },
  onNodeUnchecked: function (event, node) {
    $('#checkable-output').prepend('<p>' + node.text + ' was unchecked</p>');
  }
});

checkableTree.on('nodeChecked ', function(ev, node) {
                    for(var i in node.nodes) {console.log(node);
                        var child = node.nodes[i];
                        $(this).treeview(true).checkNode(child.nodeId);
                    }
                }).on('nodeUnchecked ', function(ev, node) {
                    for(var i in node.nodes) {
                        var child = node.nodes[i];
                        $(this).treeview(true).uncheckNode(child.nodeId);
                    }
                });

checkableTree.treeview('checkNode', [ $defaultRolesIds, { silent: $('#chk-check-silent').is(':checked') }]);

$(document).on('click','#comment-form button[type=submit]',function(){
    var nodes = checkableTree.treeview('getChecked');
    var uids = '1';
    $.each(nodes,function(index, node){
        if(node.type=='user'){
            uids= uids+','+node.user_id;
        }
    });
    $(this).parents('form').find('input#visible_uids').val(uids);
});
JS;

$this->registerJs($js);
?>
