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

<?= $form = Html::beginForm(Url::to(['pause','id'=>$modal->id]),'post',['class'=>'ajax-form']);?>
<div class="modal-body">
    <div class="form-group">
        <label for="request-accept-number" class="control-label">待定类型:</label>
        <?= Html::radioList('pause',1,['1'=>'产品待定','2'=>'地址待定']);?>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
    <button type="submit" class="btn btn-primary">提交</button>
</div>
<?= Html::endForm();?>
