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
    'action'=>Url::to(['edit-shipping-address','id'=>$model->id]),
    'options'=>['class'=>'ajax-form'],
]);?>

<div class="modal-body">
    <?= $form->field($model, 'city')->textInput();?>
    <?= $form->field($model, 'region')->textInput();?>
    <?= $form->field($model, 'telephone')->textInput();?>
    <?= $form->field($model, 'postcode')->textInput();?>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
    <button type="submit" class="btn btn-primary">提交</button>
</div>

<?php ActiveForm::end();?>
