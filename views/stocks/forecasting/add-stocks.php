<?php
/**
 * add-stocks.php
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/7/6
 */
use renk\yiipal\helpers\Html;
use renk\yiipal\helpers\Url;
?>

<?= $form = Html::beginForm(Url::to(['add-stocks','id'=>$model->id]),'post',['class'=>'ajax-form']);?>
<div class="modal-body">
    <div class="form-group">
        <label for="add-stocks-number" class="control-label">补库存数量:</label>
        <?php if($product->cid == 3)://戒指 ?>
            <?php if($product->is_couple ==1): ?>
                <input type="number" name="<?=$model->size_type?>[<?=$sizeId?>]" step=1 min="1" class="form-control" id="add-stocks-number" value="1">
            <?php else: ?>
                <input type="number" name="size[<?=$sizeId?>]" step=1 min="1" class="form-control" id="add-stocks-number" value="1">
            <?php endif;?>
        <?php else: ?>
            <input type="number" name="number" step=1 min="1" class="form-control" id="add-stocks-number" value="1">
        <?php endif;?>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
    <button type="submit" class="btn btn-primary">提交</button>
</div>
<?= Html::endForm();?>
