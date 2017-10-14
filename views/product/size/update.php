<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Modal;
use kartik\select2\Select2;
use kartik\file\FileInput;

/* @var $this yii\web\View */

?>
<div class="product-update">
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
        <?php $form = ActiveForm::begin([]);?>
        <div id="size_container">
        <?php foreach($data as $key=>$item): ?>
        <div class="row <?= $key==0?'base-row':'';?>">
            <div class="col-xs-5">
                <div class="form-group field-product-name required">
                    <label class="control-label" for="product-name">美国尺码</label>
                    <?= Html::hiddenInput('id[]',$item->id) ?>
                    <?= Html::input('text','size[]',$item->size,['class'=>'form-control']); ?>
                    <p class="help-block help-block-error"></p>
                </div>
            </div>
            <div class="col-xs-5">
                <div class="form-group field-product-name required">
                    <label class="control-label" for="product-name">尺码别名</label>
                    <?= Html::textarea('alias[]',$item->alias,['class'=>'form-control']); ?>
                    <p class="help-block help-block-error"></p>
                </div>
            </div>
            <div class="col-xs-2">
                <?= Html::button('删除',['class'=>'btn btn-xs btn-danger btn-delete confirm']);?>
            </div>
        </div>
        <?php endforeach;?>
        </div>
        <div class="row">
            <div class="col-xs-10">
                <?= Html::button('增加一行',['id'=>'add_more','class'=>'btn btn-default pull-right'])?>
            </div>
        </div>
        <?php ActiveForm::end();?>
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
    $(document).on('click','#add_more',function(){
        $(".base-row").clone().removeClass('base-row').find("input").val('').end().find('textarea').val('').end().appendTo("#size_container");
    });

    $(document).on('click','.btn-delete',function(){
        var row = $(this).parent().parent();
        if(row.hasClass('base-row')){
            alert("第一行不能删除");
        }else{
            $(this).parent().parent().slideUp().remove();
        }

    });
JS;

$this->registerJs($js);
?>
