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
fieldset>div{
    display: inline-block;
    width: 32%;
}
.color-viewer{
    background: url(/images/color-card.jpg) 0px 0px;
    background-repeat: no-repeat;
    width: 188px;
    height: 38px;
    display: inline-block;
}
CSS;
$this->registerCss($css);
?>
<?php
    $productCategories = [3=>'戒指',4=>'项链',5=>'手链',25=>'耳环'];
    $ringCategories = [0=>'单戒',1=>'对戒',2=>'套戒'];
?>
<div class="product-index">
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
                            <div><?=Html::label('版号:')?> <span><?=$model->template_no;?></span></div>
                            <?php if($model->category =='multi'): ?>
                                <div><?=Html::label('套件数:')?> <span><?=$attributesModel->rings_number;?>件套</span></div>
                            <?php endif;?>
                            <?php if(in_array($model->category,['2in1','multi','couple','single','band'])): ?>
                            <div class="rings-wrapper" style="display: block;">
                                <fieldset id="first_rings">
                                    <legend><?php if($model->category!='couple') echo '戒指';elseif($model->category=='couple')echo '男戒';else echo '主戒';?>属性</legend>
                                    <div><?=Html::label('主钻类型:')?> <span><?=Options::stoneType($attributesModel->stone_type);?></span></div>
                                    <div><?=Html::label('主钻大小:')?> <span><?=$attributesModel->stone_size;?> mm</span></div>
                                    <div><?=Html::label('主钻颜色:')?>
                                        <?php $color = Options::colorCards($attributesModel->stone_color);?>
                                        <span class="color-viewer" style="background-position-y: -<?=isset($color['position-y'])?$color['position-y']:1000000?>px"></span>
                                    </div>
                                    <div><?=Html::label('主钻克拉数:')?> <span><?=$attributesModel->stone_carat;?> 克拉</span></div>
                                    <div><?=Html::label('边钻数目:')?> <span><?=$attributesModel->side_stone_number;?> 个</span></div>
                                    <div><?=Html::label('戒指宽度:')?> <span><?=$attributesModel->width;?> mm</span></div>
                                    <div><?=Html::label('戒指厚度:')?> <span><?=$attributesModel->thickness;?> mm</span></div>
                                    <div><?=Html::label('戒指重量:')?> <span><?=$attributesModel->weight;?> g</span></div>
                                    <div><?=Html::label('电镀颜色:')?> <span><?=Options::electroplatingColor($attributesModel->electroplating_color);?> </span></div>
                                    <div style="display: block;">
                                        <?=Html::label('边钻大小:')?> <div style="margin-left: 75px;"><?=str_replace("\r\n","<br/>",$attributesModel->side_stone_size);?> </div>
                                    </div>
                                </fieldset>
                                <?php if(in_array($model->category,['2in1','multi','couple'])):?>
                                <fieldset id="second_rings">
                                    <legend><?php if($model->category=='couple')echo '女戒';else echo '副戒';?>属性</legend>
                                    <div><?=Html::label('主钻类型:')?> <span><?=Options::stoneType($attributesModel->stone_type);?></span></div>
                                    <div><?=Html::label('主钻大小:')?> <span><?=$attributesModel->stone_2_size;?> mm</span></div>
                                    <div><?=Html::label('主钻颜色:')?>
                                        <?php $color = Options::colorCards($attributesModel->stone_2_color);?>
                                        <span class="color-viewer" style="background-position-y: -<?=isset($color['position-y'])?$color['position-y']:1000000?>px"></span>
                                    </div>
                                    <div><?=Html::label('主钻克拉数:')?> <span><?=$attributesModel->stone_2_carat;?> 克拉</span></div>
                                    <div><?=Html::label('边钻数目:')?> <span><?=$attributesModel->side_stone_2_number;?> 个</span></div>
                                    <div><?=Html::label('戒指宽度:')?> <span><?=$attributesModel->width_2;?> mm</span></div>
                                    <div><?=Html::label('戒指厚度:')?> <span><?=$attributesModel->thickness_2;?> mm</span></div>
                                    <div><?=Html::label('戒指重量:')?> <span><?=$attributesModel->weight_2;?> g</span></div>
                                    <div><?=Html::label('电镀颜色:')?> <span><?=Options::electroplatingColor($attributesModel->electroplating_color_2);?> </span></div>
                                    <div style="display: block;">
                                        <?=Html::label('边钻大小:')?> <div style="margin-left: 75px;"><?=str_replace("\r\n","<br/>",$attributesModel->side_stone_2_size);?> </div>
                                    </div>

                                </fieldset>
                                <?php endif;?>
                            </div>
                            <?php endif;?>
                            <?php if($model->category=='necklace'): ?>
                            <div class="necklace-wrapper">
                                <fieldset id="necklace">
                                    <legend>项链属性</legend>
                                    <div><?=Html::label('项链长度:')?> <span><?=$attributesModel->necklace_length;?> </span></div>
                                    <div><?=Html::label('钻石个数:')?> <span><?=$attributesModel->necklace_stone_number;?> 个</span></div>
                                    <div><?=Html::label('吊坠高度:')?> <span><?=$attributesModel->necklace_pendant_height;?> 个</span></div>
                                    <div><?=Html::label('吊坠宽度:')?> <span><?=$attributesModel->necklace_pendant_width;?> 个</span></div>
                                    <div style="display: block;">
                                        <?=Html::label('边钻大小:')?> <div style="margin-left: 75px;"><?=str_replace("\r\n","<br/>",$attributesModel->necklace_stone_size);?> </div>
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
                </div>
            </div>
        </div>
    </div>
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
