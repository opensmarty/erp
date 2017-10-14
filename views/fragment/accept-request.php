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

<?= $form = Html::beginForm(Url::to(['accept-request','id'=>$modal->id]),'post',['class'=>'ajax-form']);?>
<div class="modal-body">
    <div class="form-group">
        <label for="request-accept-number" class="control-label">验收总数:</label>
        <span><?=$modal->qty_delivery;?></span>
    </div>
    <div class="form-group">
        <label for="request-accept-number" class="control-label">次品数量:</label>
        <input type="number" name="number" step=1 min="0" max="<?=$modal->qty_delivery;?>" class="form-control" id="request-accept-number" value="0">
    </div>
    <div class="form-group">
        <label class="control-label">次品原因:</label>
        <div id="reject_tags_tree"></div>
    </div>
</div>
<div class="modal-footer">
    <input type="hidden" value="" name="reject_tags" id="reject_tags">
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

        $('#reject_tags_tree')
            .jstree({
                'core' : {
                    'data' : {
                        'url' : '/category/operation?operation=get_children&parent_id=96',
                        'data' : function (node) {
                            if(node.id=='#'){
                                return { 'id' : 96 };
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
                var checkedNodes = $('#reject_tags_tree').jstree(true).get_bottom_selected();
                ids = checkedNodes.join(",");
                $("form input#reject_tags").val(ids);
            });
    });
JS;
$this->registerJs($js);
?>
