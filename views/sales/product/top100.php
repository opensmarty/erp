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
use app\helpers\ItemStatus;
use renk\yiipal\helpers\Url;
?>
<?php
$css = <<<CSS
form .btn-group .form-daterange{
    float: left;
    width: 200px;
    margin-right: 8px;
}
.magento-cid{
    position: relative;
    max-width: 200px;
}

.magento_tags_tree{
    position: fixed;
    top: 175px;
    left: 240px;
    /* right: 0px; */
    z-index: 999;
    background: #FFFFFF;
}
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
                'action'=>Url::to('top100'),
            ]);?>
                <div class="col-xs-6">
                    <table class="table" id="compare_filter_table">
                        <tr>
                            <th>分站</th>
                            <th>销售日期</th>
                            <th>产品类型</th>
                            <th>产品录入时间</th>
                        </tr>
                        <tr id="base_compare_row">
                            <td>
                                <?php $params = Yii::$app->request->get('Item',[]); ?>
                                <?=Html::dropDownList('Item[website]',isset($params['website'])?$params['website']:'',Options::websiteOptions(false,'全部'),['id'=>'website','class'=>'form-control'])?>
                            </td>
                            <td>
                                <?=Html::input('text','Item[created_at]',isset($params['created_at'])?$params['created_at']:'',['class'=>'form-control form-daterange','id'=>'created_at'])?>
                            </td>
                            <td>
                                <?php $options = [''=>'全部','rings'=>'戒指','ring_single'=>' |--单戒','ring_couple'=>' |--对戒','ring_set'=>' |--套戒','necklace'=>'项链','bracelet'=>'手链','earrings'=>'耳环'];?>
                                <?=Html::dropDownList('Item[product_type]',isset($params['product_type'])?$params['product_type']:'',$options,['class'=>'form-control'])?>
                            </td>
                            <td>
                                <?=Html::input('text','Item[product_created_at]',isset($params['product_created_at'])?$params['product_created_at']:'',['class'=>'form-control form-daterange','id'=>'product_created_at'])?>
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
                <?= \renk\yiipal\grid\GridView::widget([
                    'options'=>['class'=>'grid-view2'],
//                    'filterModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                    'columns' => [
                        [
                            'class' => 'yii\grid\CheckboxColumn',
                            'checkboxOptions' => function ($model, $key, $index, $column) {
                                return ['value' => $model->product_id];
                            },
                            'contentOptions'=>['class'=>'checkbox-column'],
                        ],
                        ['class' => 'yii\grid\SerialColumn'],
                        'sku',
                        [
                            'class' => 'yii\grid\DataColumn',
                            'contentOptions'=>['style'=>'max-width: 100px;min-width: 100px;'],
                            'label' => '图片',
                            'attribute' => 'file',
                            'value' => function ($data) {
                                $img =\renk\yiipal\helpers\FileHelper::getThumbnailWithLink($data->product->getMasterImage(),$data->id);
                                return $img;
                            },
                            'format'=>'raw',
                            'enableSorting'=>false,
                        ],
                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '销量',
                            'attribute' => 'sales_total',
                            'filter'=>false,
                            'value' => function ($data) {
                                return $data->qty_ordered;
                            },
                            'format'=>'raw',
                            'enableSorting'=>false,
                        ],
                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '选款人',
                            'attribute' => 'chosen_uid',
                            'filter'=>false,
                            'value' => function ($data) {
                                if($data->product->chosenUser){
                                    return $data->product->chosenUser->nick_name;
                                }else{
                                    return '未知';
                                }

                            },
                            'format'=>'raw',
                            'enableSorting'=>false,
                        ],

                        [
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{history}',
                            'buttons' => [
                                'history' => function($url, $model) {
                                    $url = \yii\helpers\Url::to(['/sales/product/sales-history','productId'=>$model->product_id]);
                                    return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', $url);
                                },
                            ]
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>
</div>
<?php
$js = <<<JS

JS;
$this->registerJs($js);
?>
