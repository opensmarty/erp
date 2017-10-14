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
                <?= Html::hiddenInput('Comment[visible_uids]',$availableUids,['id'=>'visible_uids']);?>
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
JS;

$this->registerJs($js);
?>
