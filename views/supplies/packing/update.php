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
                        <th style="width: 30%;">数量</th>
                        <th></th>
                    </tr>
                    <tr id="base_row">
                        <td><?= Html::activeDropDownList($model,'material_id[]',$materialOptions,['class'=>'form-control material_id']);?></td>
                        <td><?= Html::activeInput('number',$model,'price[]',['class'=>'form-control price','min'=>0,'step'=>0.01]);?></td>
                        <td><?= Html::activeInput('number',$model,'qty[]',['class'=>'form-control input-qty','min'=>1,'step'=>1]);?></td>
                        <td>
                            <button type="button" class="btn btn-default" id="add_btn">添加</button>
                            <button type="button" class="btn btn-danger hidden" id="delete_btn">删除</button>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-6">
                <span class="">总金额:&nbsp; </span><span id="total_cost">0</span>元&nbsp;
                <span class="">预付款:&nbsp; </span><span><input type="number" name="paid" min="0" step="1" value="0"/></span>
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
$(document).on("click","#add_btn",function(){
    var table = $(this).parents("table");
    var tr = table.find("tr:last-child").clone().find("input").val("").end().find("#delete_btn").removeClass("hidden").end().find("#add_btn").addClass("hidden").end();
    table.append(tr);
    return false;
});

$(document).on("click","#delete_btn",function(){
    $(this).parents("tr").remove();
    $(".input-qty").change();
    return false;
});

$(document).on("change",".material_id",function(){
    var id = $(this).val();
    var price = materialPriceOptions[id];
    $(this).parents("tr").find(".price").val(price);
    return false;
});
$(".material_id").change();

$(document).on("change",".input-qty,.price",function(){
    var total_cost = 0;
    $("table tr").each(function(index,item){
        if($(item).find(".price").length==0){
            return true;
        }
        var price = $(item).find(".price").val();
        var qty = $(item).find(".input-qty").val();
        if(qty == '') qty = 0;
        total_cost += price*parseInt(qty);
    });
    $("#total_cost").text(total_cost.toFixed(2));
});

$(".input-qty").change();

JS;

$this->registerJs($js);
?>
