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

<?= $form = Html::beginForm(Url::to(['/order/refund/create','id'=>$order->id]),'post',['class'=>'ajax-form']);?>
<div class="modal-body">
    <div class="form-group">
        <label class="control-label">退款金额:</label>
        <input type="number" name="total" step=0.01 min="0.01" max="<?=$order->grand_total;?>" class="form-control" id="request-refund-total" value="0">
    </div>
    <div class="form-group">
        <label class="control-label">退款原因:</label>
        <div id="order_refund_tags_tree"></div>
    </div>
</div>
<div class="modal-footer">
    <input type="hidden" value="" name="refund_tags" id="refund_tags">
    <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
    <button type="submit" name="submit-button" class="btn btn-primary">提交</button>
</div>
<?= Html::endForm();?>
<?php
$js = <<<JS
    $(function () {
        $(window).resize(function () {
            var h = Math.max($(window).height() - 0, 420);
            $('#container, #data, #tree, #data .content').height(h).filter('.default').css('lineHeight', h + 'px');
        }).resize();

        $('#order_refund_tags_tree')
            .jstree({
                'core' : {
                    'data' : {
                        'url' : '/category/operation?operation=get_children&parent_id=115',
                        'data' : function (node) {
                            if(node.id=='#'){
                                return { 'id' : 115 };
                            }else{
                                return { 'id' : node.id};
                            }

                        }
                    },
                    'check_callback' : true,
                    'themes': {
                        'name': 'default',
                        'responsive': true
                    }
                    //multiple: false
                },
                'force_text' : true,
                'plugins' : ['checkbox','state','dnd']
            })
            .on('state_ready.jstree', function (e, data) {
                data.instance.uncheck_all();
            })
            ;
            //提交之前，获取Tags类目
            $("form button[name=submit-button]").click(function(){
                var ids = '';
                var checkedNodes = $('#order_refund_tags_tree').jstree(true).get_bottom_selected();
                ids = checkedNodes.join(",");
                $("form input#refund_tags").val(ids);
            });
    });
JS;
$this->registerJs($js);
?>
