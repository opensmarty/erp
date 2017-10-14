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
#material-product_types .checkbox{
    display: inline-block;
}
CSS;
$this->registerCss($css);
?>
<?php

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
                <?= $form->field($model, 'name')->label('名称')->textInput() ?>
                <?= $form->field($model, 'group')->dropDownList([])?>
                <?= $form->field($model, 'type')->dropDownList([])?>
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

    //移除图片
    $(".remove-btn").click(function(){
        var imageWrapper = $(this).parent();
        var pid = $(this).data("pid");
        var fid = $(this).data("fid");
        var url = '/api/ajax/remove-attached-file';
        $.post(url,{pid:pid,fid:fid,model:'Material'},function(response){
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
