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
div.rings-wrapper{
    display: inline-block;
    min-width: 32%;
}
div.rings-wrapper.is_couple>.field-product-is_couple{
    display: block;
    max-width: 100%;
}
div.field-product-magento_cid{
    display: block;
}
#data {height: 0px;display: none;}
#data textarea { margin:0; padding:0; height:100%; width:100%; border:0; background:white; display:block; line-height:18px; }
#data, #code { font: normal normal normal 12px/18px 'Consolas', monospace !important; }
div.img-priview{
    position: relative;
    display: inline-block;
    padding: 0 10px;
}
.remove-btn{
    position: absolute;
    top: 0px;
    right: -5px;
    color: red;
    cursor: pointer;
}
CSS;
$this->registerCss($css);
?>

<div class="product-index">
    <?php $form = ActiveForm::begin([
        'options' => ['enctype' => 'multipart/form-data'],
        'enableClientValidation' => false,
        'enableAjaxValidation' => false,
    ]);?>
    <div class="body-content">
        <div class="row">
            <div class="col-xs-12">
                <table class="table" >
                    <tr>
                        <th>名称</th>
                        <th>单价</th>
                        <th>数量</th>
                    </tr>
                    <tr id="base_row">
                        <td><?= $model->name;?></td>
                        <td><?= Html::activeInput('number',$model,'price',['class'=>'form-control price','min'=>0,'step'=>0.1]);?></td>
                        <td><?= Html::activeInput('number',$model,'qty',['class'=>'form-control','min'=>1,'step'=>1]);?></td>
                    </tr>
                </table>
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
$materialPriceOptions = json_encode($materialPriceOptions);
$js = <<<JS
var materialPriceOptions =$materialPriceOptions;

$(document).on("change",".material_id",function(){
    var id = $(this).val();
    var price = materialPriceOptions[id];
    $(this).parents("tr").find(".price").val(price);
    return false;
});

JS;

$this->registerJs($js);
?>
