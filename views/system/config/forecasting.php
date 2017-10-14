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
CSS;
$this->registerCss($css);
?>

<div class="forecasting-config">
    <?php $form = ActiveForm::begin([
        'enableClientValidation' => false,
        'enableAjaxValidation' => false,
    ]);?>
    <div class="body-content">
        <div class="row">
            <div class="col-xs-12">
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <div class="form-group">
                    <label class="control-label">基于过去几天的数据预测?</label>
                    <?= Html::textInput('config[forecasting_based_date]',$model::get('forecasting_based_date',30),['type'=>'number','step'=>1,'min'=>'5','class'=>'form-control']);?>
                </div>

                <div class="form-group">
                    <label class="control-label">预测未来几天的库存?</label>
                    <?= Html::textInput('config[forecasting_date_range]',$model::get('forecasting_date_range',10),['type'=>'number','step'=>1,'min'=>'1','class'=>'form-control']);?>
                </div>

                <div class="form-group">
                    <label class="control-label">最低库存量?</label>
                    <?= Html::textInput('config[forecasting_min_number]',$model::get('forecasting_min_number',0),['type'=>'number','step'=>1,'min'=>'0','class'=>'form-control']);?>
                </div>

                <div class="form-group">
                    <label class="control-label">库存预测系数?</label>
                    <?= Html::textInput('config[forecasting_coefficient]',$model::get('forecasting_coefficient',1),['type'=>'number','step'=>0.1,'class'=>'form-control']);?>
                </div>
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
