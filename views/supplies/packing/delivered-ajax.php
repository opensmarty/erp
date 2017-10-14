<?php
/**
 * accept-request.php
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/6/3
 */
use renk\yiipal\helpers\Html;
use renk\yiipal\helpers\Url;
?>

<?= $form = Html::beginForm(Url::to(['delivered','id'=>$model->id]),'post',['class'=>'ajax-form']);?>
<div class="modal-body">
    <div class="form-group">
        <label for="request-accept-number" class="control-label">验收总数:</label>
        <input type="number" name="qty" step=1 min="0" max="<?=($model->qty-$model->qty_delivered);?>" class="form-control" value="<?=($model->qty-$model->qty_delivered);?>">
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
    <button type="submit" name="submit-button" class="btn btn-primary">提交</button>
</div>
<?= Html::endForm();?>
<?php
$js = <<<JS
JS;
$this->registerJs($js);
?>
