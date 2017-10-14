<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Modal;
use kartik\select2\Select2;
use kartik\file\FileInput;

/* @var $this yii\web\View */
$this->title = '导入物流单号';
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
                    $form->field($model, "files[0]")->label('上传物流CSV文件')->widget(FileInput::classname(), [
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
                    <?= Html::a('下载物流模板',['/download/物流模板.csv'],['class'=>'btn btn-default'])?>
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
