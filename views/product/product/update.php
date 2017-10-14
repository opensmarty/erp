<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use app\helpers\Options;
use kartik\file\FileInput;
use renk\yiipal\helpers\ArrayHelper;
/* @var $this yii\web\View */
$this->title = '产品管理';
$chosenUsersOptions = ArrayHelper::options($chosenUsers,'id','nick_name');
//$chosenUsersOptions = array_merge([''=>'未知'],$chosenUsersOptions);
$chosenUsersOptions[''] ='未知';
ksort($chosenUsersOptions);
?>
<?php
$css = <<<CSS
div.form-group{
    display: inline-block;
    min-width: 32%;
    max-width: 96%;
}
div.rings-wrapper{
    display: inline-block;
    min-width: 32%;
}
div.rings-wrapper.is_couple>.field-product-is_couple{
    display: block;
    max-width: 100%;
}
div.field-product-magento_cid{
    display: block;
}
#data {height: 0px;display: none;}
#data textarea { margin:0; padding:0; height:100%; width:100%; border:0; background:white; display:block; line-height:18px; }
#data, #code { font: normal normal normal 12px/18px 'Consolas', monospace !important; }
div.img-priview{
    position: relative;
    display: inline-block;
    padding: 0 10px;
}
.remove-btn{
    position: absolute;
    top: 0px;
    right: -5px;
    color: red;
    cursor: pointer;
}
CSS;
$this->registerCss($css);
?>

<div class="product-index">
    <?php $form = ActiveForm::begin([
        'options' => ['enctype' => 'multipart/form-data'],
        'enableClientValidation' => false,
        'enableAjaxValidation' => false,
    ]);?>
    <div class="body-content">
        <div class="row">
            <div class="col-xs-12">
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <div style="display: block;">
                    <?= $form->field($model, 'type')->label('类型')->dropDownList(['factory'=>'工厂款','taobao'=>'淘宝款','virtual'=>'虚拟商品'])?>
                    <?= $form->field($model, 'is_clean')->dropDownList(['0'=>'否','1'=>'是'])?>
                </div>
                
                <div style="display: block;">
                    <?=
                        $form->field($model, 'cid')->dropDownList($productCategories);
                    ?>
                    <div class="rings-wrapper is_couple">
                        <?= $form->field($model, 'is_couple')->dropDownList([0=>'单戒',1=>'对戒',2=>'套戒']); ?>
                    </div>
                </div>

                <?=
                    $form->field($model, 'magento_cid')->hiddenInput();
                ?>
                <div id="magento_cid_tree"></div>
                <?= $form->field($model, 'name')->label('产品名称')->textInput() ?>
                <?= $form->field($model, 'sku')->label('SKU')->textInput() ?>
                <?= $form->field($model, 'template_no')->label('版号')->textInput() ?>
                <?= $form->field($model, 'chosen_uid')->dropDownList($chosenUsersOptions) ?>
                <?= $form->field($model, 'source')->textInput() ?>
                <?= $form->field($model, 'price')->label('进货价(<span class="label-danger">对戒填写半价</span>)')->textInput()?>

                <?= $form->field($model, 'taobao_url')->label('淘宝网址')->textInput()?>
                <div class="rings-wrapper" style="display: block;">
                <?= $form->field($attributesModel, 'stone_type')->dropDownList(Options::stoneType())?>
                <?= $form->field($attributesModel, 'stone_color')->dropDownList(Options::stoneColor())?>
                <?= $form->field($attributesModel, 'stone_carat')->textInput(['type'=>'number','step'=>'0.01'])?>
                <?= $form->field($attributesModel, 'side_stone_number')->textInput(['type'=>'number','step'=>'0.01'])?>
                <?= $form->field($attributesModel, 'weight')->textInput(['type'=>'number','step'=>'0.01'])?>
                <?= $form->field($attributesModel, 'width')->textInput(['type'=>'number','step'=>'0.01'])?>
                </div>
                <div class="necklace-wrapper">
                <?= $form->field($attributesModel, 'necklace_length')->textInput(['type'=>'number','step'=>'0.01'])?>
                <?= $form->field($attributesModel, 'necklace_pendant_height')->textInput(['type'=>'number','step'=>'0.01'])?>
                <?= $form->field($attributesModel, 'necklace_pendant_width')->textInput(['type'=>'number','step'=>'0.01'])?>
                </div>
                <?=
                $form->field($model, "files[]")->widget(FileInput::classname(), [
                    'options' => ['accept' => 'image/*','overwriteInitial'=>false,'multiple' => true],
                ]);
                ?>
                <?php
                    $output ='';
                    $fids = explode(",",$model->fids);
                    $files = $model->getFiles();
                    foreach($fids as $key=>$fid){
                        $path = isset($files[$key])&&isset($files[$key]['file_path'])?$files[$key]['file_path']:'';
                        $path = str_replace("#",urlencode("#"),$path);
                        if($path){
                            $output .= '<div class="img-priview"><span class="glyphicon glyphicon-remove remove-btn" data-pid="'.$model->id.'"  data-fid="'.$fid.'"></span><img style="height: 300px;max-width: 300px;" src="/'.$path.'"/></div>';
                        }
                    }
                    echo $output;
                ?>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <div class="form-group">
                    <?= Html::submitButton('提交', ['class' => 'btn btn-primary', 'name' => 'submit-button']) ?>
                </div>
            </div>
        </div>
    </div>
    <?php ActiveForm::end();?>
</div>
<?php
$js = <<<JS
    var selectedCids = [$model->magento_cid];
    $('#product-type').change(function(){
        if($(this).val() == 'taobao'){
            $(".field-product-taobao_url").show();
        }else{
            $(".field-product-taobao_url").hide();
        }
    });
    $('#product-type').change();

    $('#product-cid').change(function(){
        //戒指
        if($(this).val() == '3'){
            $("div.rings-wrapper").show();
        }else{
            $("div.rings-wrapper").hide();
        }

        //项链
        if($(this).val() == '4'){
            $("div.necklace-wrapper").show();
        }else{
            $("div.necklace-wrapper").hide();
        }

    });
    $('#product-cid').change();

    $("#product-type").change(function(){
        var val = $(this).val();
        if(val == 'virtual'){
            $(".field-product-cid").hide();
            $(".rings-wrapper.is_couple").hide();
        }else{
            $(".field-product-cid").show();
            $(".rings-wrapper.is_couple").show();
        }
    });

    $("#product-type").change();

    $(function () {
        $(window).resize(function () {
            var h = Math.max($(window).height() - 0, 420);
            $('#container, #data, #tree, #data .content').height(h).filter('.default').css('lineHeight', h + 'px');
        }).resize();

        $('#magento_cid_tree')
            .jstree({
                'core' : {
                    'data' : {
                        'url' : '/category/operation?operation=get_node',
                        'data' : function (node) {
                            if(node.id=='#'){
                                //Magento 分类
                                return { 'id' : 6 };
                            }else{
                                return { 'id' : node.id };
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
            .bind("loaded.jstree", function (e, data) {
                data.instance.open_all();
            })
            .on('state_ready.jstree', function (e, data) {
                data.instance.uncheck_all();
                data.instance.check_node(selectedCids);

            })
            ;
    });

    //提交之前，获取Magento类目
    $("form button[name=submit-button]").click(function(){
        var ids = '';
        var checkedNodes = $('#magento_cid_tree').jstree(true).get_selected();
        ids = checkedNodes.join(",");
        $("form input#product-magento_cid").val(ids);
    });

    //移除图片
    $(".remove-btn").click(function(){
        var imageWrapper = $(this).parent();
        var pid = $(this).data("pid");
        var fid = $(this).data("fid");
        var url = '/api/ajax/remove-file';
        $.post(url,{pid:pid,fid:fid},function(response){
            if(response.status == '00'){
                imageWrapper.slideUp('normal',function(){
                    imageWrapper.remove();
                });
            }
        });
    });
JS;

$this->registerJs($js);
?>
