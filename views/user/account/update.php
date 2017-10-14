<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Modal;
/* @var $this yii\web\View */
$this->title = '我的账户';
?>
<div class="user-index">
    <?php $form = ActiveForm::begin([
        'options' => ['enctype' => 'multipart/form-data'],
        'enableClientValidation' => false,
        'enableAjaxValidation' => false,
    ]);?>
    <div class="body-content">
        <div class="row">
            <div class="col-lg-12">
            </div>
        </div>
        <div class="row">
            <div class="col-lg-4 col-md-12">
                <?= $form->field($model, 'username')->textInput(['readonly'=>'readonly']) ?>
                <?= $form->field($model, 'nick_name')->textInput() ?>
                <?= $form->field($model, 'new_password')->passwordInput()->label('新密码') ?>
                <?= $form->field($model, 'email')->textInput(['readonly'=>'readonly'])?>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12 col-dm-12">
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

JS;

$this->registerJs($js);
?>
