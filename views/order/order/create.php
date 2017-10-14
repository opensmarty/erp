<?php
use yii\helpers\Html;

use yii\bootstrap\ActiveForm;
use app\helpers\ItemStatus;

/* @var $this yii\web\View */
$this->title = '创建订单';
?>

<?php
$css = <<<CSS
table th{
    width: 100px;
}
table td div.form-group{
    display: inline-block;
    /*max-width: 45%;*/
    min-width: 32%;
}
table td div.product-list
{
border-bottom: 1px solid green;
}
CSS;

$this->registerCss($css);
?>


<div class="product-index">
    <?php $form = ActiveForm::begin([
        'enableClientValidation' => false,
        'enableAjaxValidation' => false,
        'options'=>['class'=>'ajax-form'],
    ]);?>
    <div class="body-content">
        <div class="row">
            <div class="col-lg-12">
            </div>
        </div>
        <div class="row">
            <div class="col-lg-4 col-md-12">
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12 col-dm-12">
                <table class="table table-bordered table-striped table-condensed">
                    <tr>
                        <th>订单：</th>
                        <td>
                            <?=
                            $form->field($order, 'increment_id')->textInput(['value'=>uniqid('c'),'readonly'=>'readonly']);
                            ?>
                            <?=
                            $form->field($order, 'shipping_method')->dropDownList(\app\helpers\Options::shippingMethods());
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th>创建原因</th>
                        <td>
                            <?=Html::textarea('comment','',['id'=>'editor','required'=>'required'])?>
                        </td>
                    </tr>
                    <tr>
                        <th>收货信息：</th>
                        <td>
                            <?=$form->field($address, 'region')->textInput(['required'=>'required']);?>

                            <?=$form->field($address, 'postcode')->textInput(['required'=>'required']);?>

                            <?=$form->field($address, 'street')->textInput(['required'=>'required']);?>
                            <?=$form->field($address, 'city')->textInput(['required'=>'required']);?>
                            <?=$form->field($address, 'country_id')->textInput(['required'=>'required'])->label('国家代码（两个字符：如US）');?>
                            <?=$form->field($address, 'email')->textInput(['required'=>'required']);?>
                            <?=$form->field($address, 'firstname')->textInput(['required'=>'required']);?>
                            <?=$form->field($address, 'lastname')->textInput(['required'=>'required']);?>
                            <?=$form->field($address, 'telephone')->textInput(['required'=>'required']);?>

                        </td>
                    </tr>
                    <tr>
                        <th>产品清单：</th>
                        <td id="product-list-wrapper">
                            <div class="product-list">
                            <?=$form->field($item, 'sku[]')->textInput(['required'=>'required','class'=>'form-control field-sku']);?>
                            <?=$form->field($item, 'price[]')->textInput(['required'=>'required']);?>
                            <?=$form->field($item, 'qty_ordered[]')->textInput(['required'=>'required']);?>
                            <div id="rings_attrs_wrapper" style="display: none;">
                                <?=$form->field($item, 'size_type[]')->dropDownList(['none'=>'','men'=>'男款','women'=>'女款']);?>
                                <?=$form->field($item, 'size_us[]')->dropDownList($sizeList,['class'=>'form-control select-size-us']);?>
                                <?=$form->field($item, 'size_original[]')->dropDownList(reset($sizeAliasList));?>
                            </div>
                            </div>
                        </td>
                    </tr>
                </table>
                <div class="form-group pull-right">
                    <?= Html::button('添加一行', ['class' => 'btn btn-success', 'name' => 'add-button','id'=>'btn-add-product']) ?>
                </div>
                <div class="form-group">
                    <?= Html::submitButton('提交', ['class' => 'btn btn-primary', 'name' => 'submit-button','id'=>'create-order-btn']) ?>
                </div>
            </div>
        </div>
    </div>
    <?php ActiveForm::end();?>
</div>
<?php
$sizeAliasList = json_encode($sizeAliasList);
$js = <<<JS
CKEDITOR.replace('editor');
$("#btn-add-product").click(function(){
    $("div.product-list:first").clone().find("input").val('').end().find("#rings_attrs_wrapper").hide().end().appendTo("#product-list-wrapper");
});
$(document).on("focusout",".field-sku",function(){
    var sku = $(this).val();
    var parent = $(this).parents(".product-list");
    var url = '/api/ajax/get-product-info';
    $.post(url,{sku:sku},function(response){
        if(response.status != '00'){
            return ;
        }
        var data = response.data;
        if(data == null){
            bootbox.alert("SKU不存在！");
            return ;
        }

        if(data.cid == 3){
            parent.find("#rings_attrs_wrapper").show();
            if(data.is_couple==1){
                parent.find(".field-item-size_type").show();
            }else{
                parent.find(".field-item-size_type").hide();
            }
        }else{
            parent.find("#rings_attrs_wrapper").hide();
        }
    });
});
var sizeAliasList = $sizeAliasList;
$(document).on("change",".select-size-us",function(){
    var options = sizeAliasList[$(this).val()];
    var optionsHtml = '';
    $.each(options,function(index, val){
        optionsHtml +='<option value="'+val+'">'+val+'</option>'
    });
    $(this).parent().parent().find(".field-item-size_original select#item-size_original").html(optionsHtml);
});
$("#create-order-btn").click(function(event){
    if(CKEDITOR.instances.editor.getData() == ''){
        bootbox.alert('请填写创建原因');
        event.stopPropagation();
    }
});
JS;

$this->registerJs($js);
?>
