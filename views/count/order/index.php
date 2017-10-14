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
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>订单统计</th>
                                <th colspan="2" style="background-color: #F4B084;">移动端</th>
                                <th colspan="11" style="background-color: #F8CBAD;">PC端</th>
                                <th colspan="3" style="background-color: #FCE4D6;">整站</th>
                            </tr>
                            
                            <tr>
                                <th>日期</th>
                                <th>总订单</th>
                                <th>Paypal支付</th>
                                <th>总订单</th>
                                <th>已支付</th>
                                <th>Affirm支付</th>
                                <th>Affirm支付成功率</th>
                                <th>Paypal支付</th>
                                <th>Paypal支付成功率</th>
                                <th>Pending订单</th>
                                <th>Affirm Pending</th>
                                <th>Paypal Pending</th>
                                <th>其他订单</th>
                                <th>PC支付率</th>
                                <th>总订单</th>
                                <th>已支付</th>
                                <th>支付率</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data as $k=>$v):?>
                            <tr>
                                <td><?= $k;?></td>
                                <td><?= $v['mobile_order_total']?></td>
                                <td><?= $v['mobile_payment_method_paypal_express_processing']?></td>
                                <td><?= $v['pc_order_total']?></td>
                                <td><?= $v['pc_payment_status_processing']?></td>
                                <td><?= $v['pc_payment_method_affirm_processing']?></td>
                                <td><?= $v['pc_affirm_rate']?></td>
                                <td><?= $v['pc_payment_method_paypal_express_processing']?></td>
                                <td><?= $v['pc_paypal_express_rate']?></td>
                                <td><?= $v['pc_payment_status_pending']?></td>
                                <td><?= $v['pc_payment_method_affirm_pending']?></td>
                                <td><?= $v['pc_payment_method_paypal_express_pending']?></td>
                                <td><?= $v['pc_order_other']?></td>
                                <td><?= $v['pc_processing_rate']?></td>
                                <td><?= $v['order_total']?></td>
                                <td><?= $v['order_total_processing']?></td>
                                <td><?= $v['processing_rate']?></td>
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
