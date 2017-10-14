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
CSS;
$this->registerCss($css);
?>

<div class="product-template-index">
    <div class="body-content">
        <div class="row">
            <div class="col-xs-12">
                <div class="form-groups">
                    <div class="base-attr">
                        <div class="attr-item">
                            <?php if($model->type == 1): ?>
                                <span class="product-attr-item">
                                    <?=Html::label('母版SKU:')?> <span><?=$model->based_sku;?></span>
                                </span>
                            <?php endif;?>

                            <span class="product-attr-item">
                                <?=Html::label('SKU:')?> <span><?=$model->sku;?></span>
                            </span>
                        </div>
                        <?php if(Yii::$app->user->can('/permission/template-reason')): ?>
                        <div>
                            <span class="product-attr-item">
                                <?=Html::label('选品理由:')?>
                                <div>
                                    <?=isset($model->reason_note)?$model->reason_note:'无';?>
                                </div>
                            </span>
                        </div>
                        <?php endif;?>
                        <div>
                            <span class="product-attr-item">
                                <?=Html::label('开版说明:')?>
                                <div>
                                    <?=isset($model->descriptionComment->content)?$model->descriptionComment->content:'无';?>
                                </div>
                            </span>
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
$js = <<<JS
JS;

$this->registerJs($js);
?>
