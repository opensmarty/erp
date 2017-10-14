<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use app\helpers\Options;
use kartik\file\FileInput;
use renk\yiipal\helpers\ArrayHelper;
/* @var $this yii\web\View */
$this->title = '定时任务';
?>
<?php
$css = <<<CSS
.checkbox, .radio{
display: inline-block;
}
CSS;
$this->registerCss($css);
?>

<div class="cron-index">
    <?php $form = ActiveForm::begin([
        'enableClientValidation' => false,
        'enableAjaxValidation' => false,
    ]);?>
    <div class="body-content">
        <div class="row">
            <div class="col-xs-8">
            </div>
        </div>
        <div class="row">
            <div class="col-xs-8">
                <?= $form->field($model, 'name')->label('任务名称')->textInput() ?>
                <?= $form->field($model, 'url')->label('任务模块')->textInput() ?>
                <?= $form->field($model, 'params')->label('任务参数')->textInput() ?>
                <?= $form->field($model, 'enabled')->radioList(['1'=>'启用','0'=>'关闭']) ?>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-8">
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
