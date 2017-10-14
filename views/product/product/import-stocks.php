<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Modal;
use kartik\select2\Select2;
use kartik\file\FileInput;

/* @var $this yii\web\View */
$this->title = '导入产品库存';
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
                    $form->field($model, "files[0]")->label('上传产品库存CSV文件')->widget(FileInput::classname(), [
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
                    <?= Html::a('下载产品库存模板',['/download/产品库存.csv'],['class'=>'btn btn-default'])?>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <h4>导入模板字段说明：</h4>
                <p>Size：戒指：5代表5(U.S),依次类推，非戒指：0</p>
                <p>Type：戒指：none(单戒)，men(对戒男款)，women(对戒女款)，非戒指：none</p>
                <p>Number：实际库存数量</p>
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
