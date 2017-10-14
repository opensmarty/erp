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
#data {height: 0px;display: none;}
#data textarea { margin:0; padding:0; height:100%; width:100%; border:0; background:white; display:block; line-height:18px; }
#data, #code { font: normal normal normal 12px/18px 'Consolas', monospace !important; }
CSS;
$this->registerCss($css);
?>

<div class="ip-update">
    <?php $form = ActiveForm::begin([
        'enableClientValidation' => false,
        'enableAjaxValidation' => false,
    ]);?>
    <div class="body-content">
        <div class="row">
            <div class="col-xs-12">
                <p>当前IP：<?=$ip;?></p>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <?= $form->field($model, 'ip');?>
                <?= $form->field($model, 'type');?>
                <?= $form->field($model, 'permission')->dropDownList(['deny'=>'禁止','allow'=>'允许'])?>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
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
