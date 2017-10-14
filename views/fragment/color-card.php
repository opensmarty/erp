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
<?php
$css = <<<CSS
.checkbox-span{
    float: left;
    margin-top: 10px;
    display: inline-block;
}
.span-image{
    background: url(/images/color-card.jpg) 0px 0px;
    width: 188px;
    height: 38px;
    display: inline-block;
    /*float: left;*/
}
.span-text{
    display: inline-block;
    line-height: 38px;
    height: 38px;
    padding-left: 10px;
}
.modal-footer label{
    display: inline;
}
.list-group>.list-group-item{
    min-width: 200px;
    display: inline-block;
}
CSS;
$this->registerCss($css);
?>
<?php
$colorOptions = \app\helpers\Options::colorCards();
$positionIndex = 0;
$offset = 0;
?>
<div class="modal-body">

</div>
<div class="modal-footer">
    <div class="row">
        <div class="col-xs-12">
            <input type="hidden" id="target_select_id" value="">
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12">
            <ul class="list-group" style="text-align: center;">
            <?php foreach($colorOptions as $index => $item): ?>

                    <li class="list-group-item">
                        <span class="input-group-addon2 checkbox-span">
                            <input type="radio" id="<?=$index?>" value="<?=$index?>" name="picker-color" data-position="<?=$item['position-y']?>">
                        </span>
                        <label for="<?=$index?>">
                            <span class="span-image" style="background-position-y: -<?=$offset+$positionIndex*50;?>px;"></span>
                            <span class="span-text hidden"><?=$item['label']?></span>
                        </label>
                    </li>
                    <?php $positionIndex++;if($positionIndex>25){$offset=5;} if($positionIndex>30){$offset=8;};?>
            <?php endforeach;?>
            </ul>
        </div>
    </div>
    <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
    <button type="submit" name="submit-button" class="btn btn-primary">提交</button>
</div>
<?php
$js = <<<JS
    $(".list-group-item input[type=radio]").change(function(){
        var val = $(this).val();
        var target_id = $("#target_select_id").val();
        $("#"+target_id).val(val).change();
        $('.modal').modal('hide');
        var position_y = $(this).data("position");
       // $("#"+target_id).next().css("background-position-y","-"+position_y+"px");
    });
JS;
$this->registerJs($js);
?>
