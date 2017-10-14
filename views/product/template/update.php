<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use app\helpers\Options;
use kartik\file\FileInput;
use renk\yiipal\helpers\ArrayHelper;
/* @var $this yii\web\View */

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
<?php
$websites = Options::websiteOptions();
array_pop($websites);
$electroplatingColor = Options::electroplatingColor();
$electroplatingColor = array_merge([''=>''],$electroplatingColor);
$users = \app\models\User::find()->asArray()->all();
$usersOptions = ArrayHelper::options($users,'id','nick_name');
$usersOptions = array_merge(['0'=>'未知'],$usersOptions);
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
                <?= $form->field($model,'type')->dropDownList(Options::templateTypes());?>
                <?= $form->field($model, 'based_sku')->textInput() ?>
                <div>
                    <?= $form->field($model, 'sku')->textInput() ?>
                    <?= $form->field($model, 'category')->dropDownList(Options::templateCategory()) ?>
                    <?= $form->field($model, 'is_original')->dropDownList([0=>'否',1=>'是']) ?>
                    <?= $form->field($model, 'electroplating_color')->dropDownList($electroplatingColor)?>
                    <?= $form->field($model, 'country')->dropDownList($websites) ?>
                    <?= $form->field($model, 'chosen_uid')->dropDownList($usersOptions)?>
                </div>
                <div>
                    <?= $form->field($model, 'reason_note')->textarea(['id'=>'reason_editor']) ?>
                    <div class="form-group">
                    <label class="control-label" for="producttemplate-sku">开版说明</label>
                    <?=Html::hiddenInput('comment_id',isset($model->descriptionComment->id)?$model->descriptionComment->id:'')?>
                    <?=Html::textarea('comment',isset($model->descriptionComment->content)?$model->descriptionComment->content:'',['id'=>'editor','required'=>'required'])?>
                    </div>
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
                <div id="image-gallery">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <div class="form-group">
                    <?= Html::hiddenInput('based_fids','',['id'=>'based_fids'])?>
                    <?= Html::hiddenInput('template_no','',['id'=>'template_no'])?>
                    <?= Html::submitButton('提交', ['class' => 'btn btn-primary', 'name' => 'submit-button']) ?>
                </div>
            </div>
        </div>
    </div>
    <?php ActiveForm::end();?>
</div>
<?php
$js = <<<JS
CKEDITOR.replace('editor');
CKEDITOR.replace('reason_editor');
    var renderImage = function(files){
        $("#image-gallery").html("");
        if(files == ''){
            return false;
        }
        var fids = '';
        $.each(files,function(index, item){
            var img = '<div class="img-priview"><img style="height: 300px;max-width: 300px;" src="/'+item.file_path+'"/></div>';
            $("#image-gallery").append(img);
            fids += item.id+",";
        });
        $("#based_fids").val(fids);
    };

    $("#producttemplate-type").change(function(){
        if($(this).val() == 1){
            $(".field-producttemplate-based_sku").show();
        }else{
            $(".field-producttemplate-based_sku").hide();
            $("#based_fids").val('');
            $("#image-gallery").html("");
        }
    });
    $("#producttemplate-type").change();

    $("#producttemplate-based_sku").focusout(function(){
        var based_sku = $(this).val();
        var url = '/api/ajax/get-template';
        $.post(url,{based_sku:based_sku},function(response){
            if(response.status == '0'){
                bootbox.alert(response.msg);
            }else{
                var files = response.data.files;
                renderImage(files);
                $("#template_no").val(response.data.template_no);
            }
        });
    });

    //移除图片
    $(".remove-btn").click(function(){
        var imageWrapper = $(this).parent();
        var pid = $(this).data("pid");
        var fid = $(this).data("fid");
        var url = '/api/ajax/remove-template-file';
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
