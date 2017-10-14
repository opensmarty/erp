<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use app\helpers\Options;
use kartik\file\FileInput;
use renk\yiipal\helpers\ArrayHelper;
/* @var $this yii\web\View */
$this->title = '开版管理';

?>
<?php
$css = <<<CSS
.attr-item{
    border-bottom: solid 1px gray;
    margin-bottom: 15px;
}
.product-attr-item{
    display: inline-block;
    min-width: 32%;
}
div.form-group {
    display: inline-block;
    min-width: 32%;
    max-width: 96%;
}
.form-group {
    margin-bottom: 15px;
}
fieldset{
    border: solid 1px gray;
    padding: 6px;
}
legend{
    width: auto;
    margin-bottom:8px;
}
.product-image{
    padding: 6px 0;
}
.img-priview{
float: left;
}
.color-picker{
    background: url(/images/color-card.jpg) 0px 100px;
    background-repeat: no-repeat;
    width: 188px;
    height: 38px;
}
CSS;
$this->registerCss($css);
?>
<?php
    $productCategories = [3=>'戒指',4=>'项链',5=>'手链',25=>'耳环'];
    $ringCategories = [0=>'单戒',1=>'对戒',2=>'套戒'];
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

            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <div class="form-groups">
                    <div class="base-attr">
                        <div class="attr-item">
                            <span class="product-attr-item">
                                <?=Html::label('产品分类:')?> <span><?=Options::templateCategory($model->category)?></span>
                            </span>
                            <span class="product-attr-item">

                            </span>
                        </div>

                        <div class="attr-update">
                            <?= $form->field($model, 'template_no')->label('版号')->textInput() ?>
                            <?php if($model->category =='multi'): ?>
                                <?= $form->field($attributesModel, 'rings_number')->label('套件数')->dropDownList(['2'=>'2件套','3'=>'3件套','4'=>'4件套','5'=>'5件套']) ?>
                            <?php endif;?>
                            <?php if(in_array($model->category,['2in1','multi','couple','single','band'])): ?>
                            <div class="rings-wrapper" style="display: block;">
                                <fieldset id="first_rings">
                                    <legend><?php if($model->category!='couple') echo '戒指';elseif($model->category=='couple')echo '男戒';else echo '主戒';?>属性</legend>
                                    <?= $form->field($attributesModel, 'stone_type')->dropDownList(Options::stoneType())?>
                                    <?= $form->field($attributesModel, 'stone_size')->textInput()->label('主钻大小(mm)-如：6*6')?>
                                    <div class="form-group field-producttemplateattributes-stone_color">
                                        <label class="control-label" for="producttemplateattributes-stone_color">主钻颜色</label>
                                        <?= Html::activeHiddenInput($attributesModel,'stone_color',['class'=>'color-picker-target'])?>
                                        <input type="text" readonly class="form-control color-picker" data-id="producttemplateattributes-stone_color"/>
                                        <p class="help-block help-block-error"></p>
                                    </div>
                                    <?= $form->field($attributesModel, 'stone_carat')->textInput(['type'=>'number','step'=>'0.01'])?>
                                    <?= $form->field($attributesModel, 'side_stone_number')->textInput(['type'=>'number','step'=>'1'])->label('边钻个数')?>
                                    <?= $form->field($attributesModel, 'width')->textInput(['type'=>'number','step'=>'0.01'])->label('戒指宽度(mm)')?>
                                    <?= $form->field($attributesModel, 'thickness')->textInput(['type'=>'number','step'=>'0.01'])->label('戒指厚度(mm)')?>
                                    <?= $form->field($attributesModel, 'weight')->textInput(['type'=>'number','step'=>'0.01'])->label('戒指重量(g)')?>
                                    <?= $form->field($attributesModel, 'electroplating_color')->dropDownList(Options::electroplatingColor())->label('电镀颜色')?>
                                    <div>
                                    <?= $form->field($attributesModel, 'side_stone_size')->textarea()->label('边钻大小(mm)-如：6*6-12 (每行一种尺码)')?>
                                    </div>
                                </fieldset>
                                <?php if(in_array($model->category,['2in1','multi','couple'])):?>
                                <fieldset id="second_rings">
                                    <legend><?php if($model->category=='couple')echo '女戒';else echo '副戒';?>属性</legend>
                                    <?= $form->field($attributesModel, 'stone_2_type')->dropDownList(Options::stoneType())->label('主钻类型')?>
                                    <?= $form->field($attributesModel, 'stone_2_size')->textInput()->label('主钻大小(mm)-如：6*6')?>
                                    <div class="form-group field-producttemplateattributes-stone_color">
                                        <label class="control-label" for="producttemplateattributes-stone_2_color">主钻颜色</label>
                                        <?= Html::activeHiddenInput($attributesModel,'stone_2_color',['class'=>'color-picker-target'])?>
                                        <input type="text" readonly class="form-control color-picker" data-id="producttemplateattributes-stone_2_color"/>
                                        <p class="help-block help-block-error"></p>
                                    </div>
                                    <?= $form->field($attributesModel, 'stone_2_carat')->textInput(['type'=>'number','step'=>'0.01'])->label('主钻克拉数')?>
                                    <?= $form->field($attributesModel, 'side_stone_2_number')->textInput(['type'=>'number','step'=>'1'])->label('边钻数量')?>
                                    <?= $form->field($attributesModel, 'width_2')->textInput(['type'=>'number','step'=>'0.01'])->label('戒指宽度(mm)')?>
                                    <?= $form->field($attributesModel, 'thickness_2')->textInput(['type'=>'number','step'=>'0.01'])->label('戒指厚度(mm)')?>
                                    <?= $form->field($attributesModel, 'weight_2')->textInput(['type'=>'number','step'=>'0.01'])->label('戒指重量(g)')?>
                                    <?= $form->field($attributesModel, 'electroplating_color_2')->dropDownList(Options::electroplatingColor())->label('电镀颜色')?>
                                    <div>
                                    <?= $form->field($attributesModel, 'side_stone_2_size')->textarea()->label('边钻大小(mm)-如：6*6-12 (每行一种尺码)')?>
                                    </div>
                                </fieldset>
                                <?php endif;?>
                            </div>
                            <?php endif;?>
                            <?php if($model->category=='necklace'): ?>
                            <div class="necklace-wrapper">
                                <fieldset id="necklace">
                                    <legend>项链属性</legend>
                                    <?= $form->field($attributesModel, 'necklace_length')->textInput(['type'=>'number','step'=>'0.01'])?>
                                    <?= $form->field($attributesModel, 'necklace_stone_number')->textInput(['type'=>'number','step'=>'1'])->label('钻石个数')?>
                                    <?= $form->field($attributesModel, 'necklace_pendant_height')->textInput(['type'=>'number','step'=>'0.01'])?>
                                    <?= $form->field($attributesModel, 'necklace_pendant_width')->textInput(['type'=>'number','step'=>'0.01'])?>
                                    <div>
                                    <?= $form->field($attributesModel, 'necklace_stone_size')->textarea()->label('钻石大小(mm)-如：6*6-12 (每行一种尺码)')?>
                                    </div>
                                </fieldset>
                            </div>
                            <?php endif;?>
                        </div>

                        <div class="product-image">
                            <?php
                            $output ='';
                            $fids = explode(",",$model->fids);
                            $files = $model->getFiles();
                            foreach($fids as $key=>$fid){
                                $path = isset($files[$key])&&isset($files[$key]['file_path'])?$files[$key]['file_path']:'';
                                $path = str_replace("#",urlencode("#"),$path);
                                if($path){
                                    $output .= '<div class="img-priview"><img style="height: 300px;max-width: 300px;" src="/'.$path.'"/></div>';
                                }
                            }
                            echo $output.'<div class="clear-both"></div>';
                            ?>
                        </div>
                    </div>
                    <?= Html::submitButton('提交', ['class' => 'btn btn-primary clear-both', 'name' => 'submit-button']) ?>
                </div>
            </div>
        </div>
    </div>
    <?php ActiveForm::end();?>
</div>
<?php
$options = json_encode(Options::colorCards());
$js = <<<JS
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
    var colorOptions=$options;
    $(".color-picker-target").change(function(){
        var val = $(this).val();
        var position = colorOptions[val]['position-y'];
        $(this).next().css("background-position-y","-"+position+"px");
    }).change();
JS;

$this->registerJs($js);
?>
