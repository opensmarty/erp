<?php
use yii\bootstrap\ActiveForm;
use kartik\file\FileInput;
use renk\yiipal\helpers\Html;

/* @var $this yii\web\View */
$this->title = '导出产品';
?>
<div class="product-index">
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
                <?=
                    $form->field($model, "files[0]")->label('根据SKU导出产品')->widget(FileInput::classname(), [
                        'options' => ['accept' => 'csv','overwriteInitial'=>false],
                        'data'=>'',
                    ]);
                ?>
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
$(function(){
    var path = '$path';
    if(path){
        window.open(path);
    }
})
JS;

$this->registerJs($js);
?>
