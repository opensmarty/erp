<?php
/**
 * index.php
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/7/15
 */
use renk\yiipal\helpers\Html;
use app\helpers\Options;
use yii\bootstrap\ActiveForm;
use renk\yiipal\helpers\Url;
?>
<?php
$css = <<<CSS
form .btn-group .form-daterange{
    float: left;
    width: 200px;
    margin-right: 8px;
}
th,td{ text-align:center}

CSS;
$this->registerCss($css);

?>
<div class="loan-index">
    <div class="body-content">
        <div class="row btn-group-top">
            <?php $form = ActiveForm::begin([
                'enableClientValidation' => false,
                'enableAjaxValidation' => false,
                'method'=>'get',
                'action'=>Url::to('index'),
                'id'=>'searchForm',
            ]);?>
                <div class="col-xs-6">
                    <table class="table" id="compare_filter_table">
                        <tr>
                            <th>分站</th>
                            <th>销售日期</th>
                            <th></th>
                        </tr>
                        <tr id="base_compare_row">
                            <td><?=Html::dropDownList('source',Yii::$app->request->get('source'),Options::websiteOptions(false,'全部'),['id'=>'website','class'=>'form-control'])?></td>
                            
                            <td>
                                <?php $params = Yii::$app->request->get('Order',[]); ?>
                                <?=Html::input('text','Order[created_at]',isset($params['created_at'])?$params['created_at']:'',['class'=>'form-control form-daterange','id'=>'created_at'])?>
                            </td>
                            <td>
                                <?= Html::submitButton('查询',['class'=>'btn btn-primary','id'=>'product_top100_btn'])?>
                            </td>
                        </tr>
                    </table>
                </div>
            <?php ActiveForm::end();?>
            <div class="col-xs-6">
                <div class="btn-group pull-right">
                    <div class="btn-group-top">
                        <?= Html::authSubmitButton('导出', ['export'], ['class' => 'btn btn-default download','form-class'=>'ajax-form download', 'data-action-before'=>'get_ids']) ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12">
                <div id="w0" class="grid-view">
                    <?php foreach ($data as $k=>$v):?>
                    <?php $i = 0;?>
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th><?= $k;?></th>
                                <th>总使用量</th>
                                <th>M使用量</th>
                                <th>PC使用量</th>
                                <th>coupon使用量占比</th>
                                <th>coupon量与总订单占比</th>
                                <th>总订单数</th>
                                <th>M订单数</th>
                                <th>PC订单数</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($v as $coupon=>$number):?>
                                <tr>
                                    <td><?= $number['coupon_code'];?></td>
                                    <td><?= $number['used']?></td>
                                    <td><?= $number['used_mobile']?></td>
                                    <td><?= $number['used_pc']?></td>
                                    <td><?= $number['used_rate_percent']?></td>
                                    <td><?= $number['total_rate_percent']?></td>
                                    <?php if($i == 0){?>
                                        <td rowspan="<?= count($v)-2?>" style="vertical-align: middle;"><?= $number['total']?></td>
                                        <td rowspan="<?= count($v)-2?>" style="vertical-align: middle;"><?= $number['total_mobile']?></td>
                                        <td rowspan="<?= count($v)-2?>" style="vertical-align: middle;"><?= $number['total_pc']?></td>
                                    <?php } else if(count($v) - $i <= 2){?>
                                        <td><?= $number['total']?></td>
                                        <td><?= $number['total_mobile']?></td>
                                        <td><?= $number['total_pc']?></td>
                                    <?php }?>
                                    <?php $i++;?>
                                </tr>
                            <?php endforeach;?>
                        </tbody>
                    </table>
                    <?php endforeach;?>
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
