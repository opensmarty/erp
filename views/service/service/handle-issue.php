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
    /*display: inline-block;*/
    /*min-width: 32%;*/
    /*max-width: 96%;*/
}
div.issue-list-item{
    border: dashed 1px rgba(128, 128, 128, 0.46);
    padding: 10px;
    margin: 4px 0;
}
.issue-body{
    word-wrap: break-word;
    word-break: break-all;
    overflow-y: auto;
    max-height: 100px;
}
.solved-button-wrapper{
    float: right;
    margin: 0 10px;
    padding: 4px;
}
CSS;
$this->registerCss($css);
?>

<div class="service-issue-index">
    <?php $form = ActiveForm::begin([
//        'options' => ['enctype' => 'multipart/form-data'],
        'enableClientValidation' => false,
        'enableAjaxValidation' => false,
    ]);?>
    <div class="body-content">
        <div class="row">
            <div class="col-xs-12">
            </div>
        </div>
        <div class="row">
            <div class="col-xs-9">
                <?= $form->field($model, 'subject')->textInput(['required'=>'required']);?>
                <div>
                    <?= $form->field($model, 'content')->textarea(['id'=>'editor']);?>
                </div>
            </div>
            <div class="col-xs-3">
                <?php if(!empty($solutions)):?>
                <div class="list-group2">
                    <div>可用的处置方案建议：</div>
                    <?php foreach($solutions as $index => $solution): ?>
                        <div><?=$index+1?>. <a href="/service/service/solution-view?id=<?=$solution->id;?>" class="list-group-item2"><?=$solution->subject;?></a></div>
                    <?php endforeach; ?>
                </div>
                <?php else:?>
                    <span>暂时没有处置建议，可以咨询部门经理获取处置方案。</span>
                <?php endif;?>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-9">
                <div class="form-group">
                    <?= Html::submitButton('提交', ['class' => 'btn btn-primary pull-right', 'name' => 'submit-button']) ?>
                    <span class="solved-button-wrapper"><?= Html::checkbox('solved',false,['id'=>'solved-checkbox']);?> <label for="solved-checkbox" style="color: red;">已经解决</label></span>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-9">
                <?php foreach($items as $item):?>
                    <div class="issue-list-item">
                        <div class="issue-subject"><h4><?= $item->subject?></h4></div>
                        <div class="issue-body"><?=$item->content?></div>
                        <div class="issue-footer text-right"><span class="issue-reporter"><?=$item->user->nick_name;?></span> - <span class="issue-date"><?= date("Y-m-d H:i:s",$item->created_at);?></span></div>
                    </div>
                <?php endforeach;?>
            </div>
        </div>
    </div>
    <?php ActiveForm::end();?>
</div>
<?php
$js = <<<JS
CKEDITOR.replace('editor');
JS;

$this->registerJs($js);
?>
