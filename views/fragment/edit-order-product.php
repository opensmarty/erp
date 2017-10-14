<?php
/**
 * edit-shipping-method.php
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/6/3
 */
use renk\yiipal\helpers\Html;
use renk\yiipal\helpers\Url;
use yii\bootstrap\ActiveForm;
?>

<?php $form = ActiveForm::begin([
    'enableClientValidation' => false,
    'enableAjaxValidation' => false,
    'action'=>Url::to([$action,'id'=>$model->id]),
    'options'=>['class'=>'ajax-form'],
]);?>

<div class="modal-body">
    <?php if($field == 'qty_ordered'): ?>
        <?php if($model->order->source == 'SYS'): ?>
            <?= $form->field($model, $field)->textInput(['type'=>'number','min'=>'1','step'=>'1']);?>
        <?php else:?>
            <?= $form->field($model, $field)->textInput(['type'=>'number','min'=>'1','max'=>$model->qty_ordered,'step'=>'1']);?>
        <?php endif;?>
    <?php else:?>
        <?= $form->field($model, $field)->textInput();?>
    <?php endif;?>

</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
    <button type="submit" class="btn btn-primary keep-modal">提交</button>
</div>

<?php ActiveForm::end();?>
