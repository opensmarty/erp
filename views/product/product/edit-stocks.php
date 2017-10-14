<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Modal;
use kartik\select2\Select2;
use kartik\file\FileInput;

/* @var $this yii\web\View */
$this->title = '编辑库存';
$stocks = $product->getProductStocks();
?>
<div class="add-stock content">
    <?php $form = ActiveForm::begin([
        'enableClientValidation' => false,
        'enableAjaxValidation' => false,
        'options' => ['id'=>'add-stock-form','class'=>'ajax-form'],
    ]);?>
    <div class="body-content">
        <div class="row">
            <div class="col-lg-12">
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <?php if($product->cid == 3)://戒指 ?>
                    <table class="table table-striped table-bordered">
                        <tr>
                            <th>实际库存量</th>
                            <th>修正库存量</th>
                            <th>US尺码</th>
                        </tr>
                        <?php if($product->is_couple ==1): ?>
                            <tr><td colspan="3">男款</td></tr>
                            <?php foreach($sizeList as $size): ?>
                                <tr>
                                    <td>
                                        <?php
                                            $realStocks = 0;
                                            $actualTotal = 0;
                                            $virtualTotal = 0;
                                            if(!empty($stocks)){
                                                if(isset($stocks['men'][$size->id])){
                                                    $realStocks = isset($stocks['men'][$size->id])?$stocks['men'][$size->id]['total']:0;
                                                    $actualTotal = intval($stocks['men'][$size->id]['actual_total']);
                                                    $virtualTotal = intval($stocks['men'][$size->id]['virtual_total']);
                                                }
                                            }
                                            $displayLabel = $realStocks."(".$actualTotal."/".$virtualTotal.")";
                                            echo $displayLabel;
                                        ?>
                                    </td>
                                    <td><?= Html::textInput('men['.$size->id.']',$actualTotal,['type'=>'number','step'=>1]) ?></td>
                                    <td><?= $size->size; ?></td>
                                </tr>
                            <?php endforeach;?>
                            <tr><td colspan="3">女款</td></tr>
                            <?php foreach($sizeList as $size): ?>
                                <tr>
                                    <td>
                                        <?php
                                        $realStocks = 0;
                                        $actualTotal = 0;
                                        $virtualTotal = 0;
                                        if(!empty($stocks)){
                                            if(isset($stocks['women'][$size->id])){
                                                $realStocks = isset($stocks['women'][$size->id])?$stocks['women'][$size->id]['total']:0;
                                                $actualTotal = intval($stocks['women'][$size->id]['actual_total']);
                                                $virtualTotal = intval($stocks['women'][$size->id]['virtual_total']);
                                            }
                                        }
                                        $displayLabel = $realStocks."(".$virtualTotal."/".$virtualTotal.")";
                                        echo $displayLabel;
                                        ?>
                                    </td>
                                    <td><?= Html::textInput('women['.$size->id.']',$actualTotal,['type'=>'number','step'=>1]) ?></td>
                                    <td><?= $size->size; ?></td>
                                </tr>
                            <?php endforeach;?>
                        <?php else: ?>
                            <?php foreach($sizeList as $size): ?>
                                <tr>
                                    <td>
                                        <?php
                                        $realStocks = 0;
                                        $actualTotal = 0;
                                        $virtualTotal = 0;
                                        if(!empty($stocks)){
                                            if(isset($stocks['none'][$size->id])){
                                                $actualTotal = intval($stocks['none'][$size->id]['actual_total']);
                                                $virtualTotal = intval($stocks['none'][$size->id]['virtual_total']);
                                            }
                                            $realStocks = isset($stocks['none'][$size->id])?$stocks['none'][$size->id]['total']:0;
                                        }
                                        $displayLabel = $realStocks."(".$actualTotal."/".intval($virtualTotal).")";
                                        echo $displayLabel;
                                        ?>
                                    </td>
                                    <td><?= Html::textInput('size['.$size->id.']',$actualTotal,['type'=>'number','step'=>1]) ?></td>
                                    <td><?= $size->size; ?></td>
                                </tr>
                            <?php endforeach;?>
                        <?php endif; ?>
                    </table>
                <?php else: ?>
                    <table class="table table-striped table-bordered">
                        <tr>
                            <th>当前库存量</th>
                            <th>补充量</th>
                        </tr>
                        <tr>
                            <td>
                                <?php
                                $realStocks = 0;
                                $actualTotal = 0;
                                $virtualTotal = 0;
                                if(!empty($stocks)){
                                    $realStocks = isset($stocks['none'][0])?$stocks['none'][0]['total']:0;
                                    if(isset($stocks['none'][0])){
                                        $actualTotal = intval($stocks['none'][0]['actual_total']);
                                        $virtualTotal = intval($stocks['none'][0]['virtual_total']);
                                    }
                                }
                                $displayLabel = $realStocks."(".$actualTotal."/".intval($virtualTotal).")";
                                echo $displayLabel;
                                ?>
                            </td>
                            <td><?= Html::textInput('number',$actualTotal,['type'=>'number','step'=>1]); ?></td>
                        </tr>
                    </table>
                <?php endif;?>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <div class="form-group pull-right">
                    <?= Html::submitButton('提交', ['class' => 'btn btn-primary', 'name' => 'submit-button']) ?>
                </div>
            </div>
        </div>
    </div>
    <?php ActiveForm::end();?>
</div>
<?php
$js = <<<JS

JS;

$this->registerJs($js);
?>
