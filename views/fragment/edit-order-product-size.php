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
    'action'=>Url::to(['edit-order-product-size','id'=>$model->id]),
    'options'=>['class'=>'ajax-form'],
]);?>

<div class="modal-body">
<?= $form->field($model, 'size_us')->dropDownList($sizeList);?>
<?php
    $sizeAliasOptions = [];
    if(isset($sizeAliasList[$model->size_us])){
        $sizeAliasOptions = $sizeAliasList[$model->size_us];
    }
?>
<?= $form->field($model, 'size_original')->dropDownList($sizeAliasOptions);?>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
    <button type="submit" class="btn btn-primary keep-modal">提交</button>
</div>

<?php ActiveForm::end();?>
<?php
$sizeAliasList = json_encode($sizeAliasList);
$js = <<<JS
    var sizeAliasList = $sizeAliasList;
    $("#item-size_us").change(function(){
        var options = sizeAliasList[$(this).val()];
        var optionsHtml = '';
        $.each(options,function(index, val){
            optionsHtml +='<option value="'+val+'">'+val+'</option>'
        });
        $("#item-size_original").html(optionsHtml);
    });
JS;
$this->registerJs($js);
?>