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
use renk\yiipal\helpers\FileHelper;
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
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th></th>
                                <?php foreach($searchModel->select_magento_cid as $magento_cid_name):?>
                                    <th colspan="3"><?= $magento_cid_name?></th>
                                <?php endforeach;?>
                            </tr>
                            <tr>
                                <th></th>
                                <?php foreach($searchModel->select_magento_cid as $magento_cid_name):?>
                                    <th>SKU</th>
                                    <th>图片</th>
                                    <th>数量</th>
                                <?php endforeach;?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data as $k=>$v):?>
                            <tr>
                                <td><?= $v['top']?></td>
                                <?php $j=0;?>
                                <?php foreach($searchModel->select_magento_cid as $magento_cid_name):?>
                                    <td><?= $v['sku'.$j]?></td>
                                    <td><?= !empty($v['image'.$j]) ? FileHelper::getThumbnailImage($v['image'.$j]) : ''?></td>
                                    <td><?= $v['count'.$j]?></td>
                                    <?php $j++?>
                                <?php endforeach;?>
                            </tr>
                            <?php endforeach;?>
                        </tbody>
                    </table>
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
