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
use yii\bootstrap\ActiveForm;
use renk\yiipal\helpers\Url;
?>

<?php $form = ActiveForm::begin([
    'enableClientValidation' => false,
    'enableAjaxValidation' => false,
    'action'=>Url::to([$action,'id'=>$order->id]),
    'options'=>['class'=>'ajax-form'],
]);?>

<div class="modal-body">
    <div class="form-group field-editor required">
        <label class="control-label" >物流公司</label>
        <?= Html::textInput('shipping_method','',['required'=>'required']); ?>
    </div>
    <div class="form-group field-editor required">
        <label class="control-label" >物流单号</label>
        <?= Html::textInput('shipping_track_no','',['required'=>'required']); ?>
    </div>
    <div class="group-list">
        <?php foreach($items as $item): ?>
            <div class="list-item">
                <?= Html::checkbox('item_ids[]',false,['label'=>$item->sku.' &nbsp;款式:'.\app\helpers\Options::ringTypes($item->size_type),'value'=>$item->id]);?>
            </div>
        <?php endforeach;?>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
    <button type="submit" class="btn btn-primary keep-modal">提交</button>
</div>

<?php ActiveForm::end();?>
