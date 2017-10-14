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
?>

<?= $form = Html::beginForm(Url::to(['edit-shipping-method','id'=>$id]),'post',['class'=>'ajax-form']);?>
<div class="modal-body">
    <div class="form-group">
        <label for="request-accept-number" class="control-label">物流方式:</label>
        <?= Html::dropDownList('shipping_method','EUB',\app\helpers\Options::shippingMethods())?>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
    <button type="submit" class="btn btn-primary">提交</button>
</div>
<?= Html::endForm();?>
