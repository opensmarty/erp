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
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="exampleModalLabel">打印</h4>
</div>
<div class="modal-body">
    <div class="form-group text-center">
        <?=Html::a("打印面单",Url::to(['/shipment/shipment/print-image-label','orderIds'=>$orderIds]),['class'=>'btn btn-primary','target'=>'_blank'])?>
        <?=Html::a("打印发票",Url::to(['/shipment/shipment/print-invoice','orderIds'=>$orderIds]),['class'=>'btn btn-success','target'=>'_blank'])?>
        <?=Html::a("打印留底联",Url::to(['/shipment/shipment/print-ups-copy','orderIds'=>$orderIds]),['class'=>'btn btn-warning','target'=>'_blank'])?>
    </div>
    <?php if(!empty($invalidAddress)): ?>
    <div class="form-group text-center text-danger">有部分订单地址可能需要调整:</div>
    <div class="form-group text-center text-danger"><?= implode(",",$invalidAddress); ?></div>
    <?php endif;?>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
</div>
