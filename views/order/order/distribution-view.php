<?php
use renk\yiipal\helpers\Html;
use yii\widgets\DetailView;
use app\helpers\ItemStatus;
use renk\yiipal\helpers\StrHelper;
use app\helpers\CommonHelper;
use yii\helpers\Url;
?>

<?php
$css = <<<css
table th{
    max-width: 200px;
}

table .first_tr{
    border-top: 2px solid darkorange;
}

css;

$this->registerCss($css);

//检查Item编辑条件
function canEditItem($model){
    return false;
    if(in_array($model->item_status,['pending','cancelled','shipped','complete','return_completed_part','return_completed']) || in_array($model->order->status,['cancelled'])){
        return false;
    }else{
        return true;
    }
}
//检查Order编辑条件
function canEditOrder($model){
    if(in_array($model->status,['pending','cancelled','shipped','complete','return_completed_part','return_completed'])){
        return false;
    }else{
        return true;
    }
}
?>

<div class="order-view">
    <p>订单信息</p>
    <table class="table table-striped table-bordered detail-view">
        <tboday>
            <tr>
                <th>编号</th>
                <td  class="text-height"><?= $model->ext_order_id;?></td>
                <th>订单号</th>
                <td><?= $model->increment_id;?></td>
            </tr>
            <tr>
                <th>创建时间</th>
                <td><?= date("Y-m-d H:i:s",$model->created_at);?></td>
                <th>付款状态</th>
                <td><?= $model->payment_status;?></td>
            </tr>
            <tr>
                <th>物流方式</th>
                <td><span class="text-height"><?= $model->shipping_method;?> <?= $model->shipping_track_no?:'';?></span> </td>
                <th>订单来源</th>
                <td><?= strtoupper($model->source);?></td>
            </tr>

        </tboday>
    </table>
    <?php if(!empty($model->shipping_track_no)): ?>
    <p>运单信息</p>
    <table class="table table-striped table-bordered detail-view">
        <tboday>
            <tr>
                <th>运单号</th>
                <td  class="text-height"><?= $model->shipping_track_no;?></td>
                <td><?= Html::a("打印标签",Url::to(["/shipment/shipment/print-image-label",'orderIds'=>$model->id]));?></td>
                <td><?= Html::a("打印发票",Url::to(["/shipment/shipment/print-invoice",'orderIds'=>$model->id]));?></td>
                <td><?= Html::a("打印留底联",Url::to(["/shipment/shipment/print-ups-copy",'orderIds'=>$model->id]));?></td>
            </tr>
        </tboday>
    </table>
    <?php endif; ?>
    <p>收货信息 <?php if(canEditOrder($model)) echo Html::authLinkWithComment('','/comment/create?target_id='.$model->id.'&type=order&subject='.\app\models\Comment::COMMENT_TYPE_CHANGE_ADDRESS,'/order/order/edit-shipping-address-directly?id='.$model->id);?></p>
    <table class="table table-striped table-bordered detail-view">
        <tr>
            <th>收货地址</th>
            <td><?= CommonHelper::filterEmptyStr($model->address->country_id) .' '. CommonHelper::filterEmptyStr($model->address->city).' '. CommonHelper::filterEmptyStr($model->address->region).' '.CommonHelper::filterEmptyStr($model->address->street);?>
                <?= CommonHelper::filterEmptyStr($model->address->company);?>
            </td>
        </tr>
        <tr>
            <th>收货人</th>
            <td><?= CommonHelper::filterEmptyStr($model->address->firstname) .' '. CommonHelper::filterEmptyStr($model->address->middlename).' '. CommonHelper::filterEmptyStr($model->address->lastname);?>
                <?= CommonHelper::filterEmptyStr($model->address->company);?>
            </td>
        </tr>
        <tr>
            <th>电话</th>
            <td class="text-height"><?= CommonHelper::filterEmptyStr($model->address->telephone);?>
            </td>
        </tr>
        <tr>
            <th>Email</th>
            <td><?= CommonHelper::filterEmptyStr($model->address->email);?>
            </td>
        </tr>
        <tr>
            <th>邮编</th>
            <td><?= CommonHelper::filterEmptyStr($model->address->postcode);?>
            </td>
        </tr>
    </table>

    <p>产品信息</p>
    <table class="table table-striped table-bordered detail-view">
        <?php foreach($model->items as $item): ?>
            <tr class="first_tr">
                <th>SKU</th>
                <td>
                    <?= $item->sku;?>
                    <?php if(canEditItem($item)) echo Html::authLinkWithComment('','/comment/create?target_id='.$model->id.'&type=order&subject='.\app\models\Comment::COMMENT_TYPE_CHANGE_PRODUCT,'/order/order/edit-order-sku?id='.$item->id);?>
                </td>
                <th>产品名称</th>
                <td><?= $item->product->name;?></td>
            </tr>
            <tr>
                <th>产品类型</th>
                <td><?= \app\helpers\Options::productTypes($item->product->type);?></td>
                <th>下单数量</th>
                <td>
                    <span class="text-height"><?= $item->qty_ordered;?></span>
                    <?php if(canEditItem($item)) echo Html::authLinkWithComment('','/comment/create?target_id='.$model->id.'&type=order&subject='.\app\models\Comment::COMMENT_TYPE_CHANGE_PRODUCT_NUMBER,'/order/order/edit-order-quantity?id='.$item->id);?>
                </td>
            </tr>
            <tr>
                <th>尺码</th>
                <td>
                    <?php if($item->size_type!= 'none') echo '<span class="text-height">'.\app\helpers\Options::ringTypes($item->size_type).'</span>'; ?>
                    网站尺码：<span class="text-height" style="font-size: 20px;"><?= $item->product->cid==3?$item->size_original:'无'?></span> 实际尺码：<?=$item->product->cid==3?$item->size_us:'无'?>
                    <?php if(canEditItem($item)) echo Html::authLinkWithComment('','/comment/create?target_id='.$model->id.'&type=order&subject='.\app\models\Comment::COMMENT_TYPE_CHANGE_PRODUCT_SIZE,'/order/order/edit-order-product-size?id='.$item->id);?>
                </td>
                <th>刻字</th>
                <td>
                    <span class="text-height"><?= $item->engravings?></span>
                    <?php if(canEditItem($item)) echo Html::authLinkWithComment('','/comment/create?target_id='.$model->id.'&type=order&subject='.\app\models\Comment::COMMENT_TYPE_CHANGE_PRODUCT_ENGRAVINGS,'/order/order/edit-order-engravings?id='.$item->id);?>
                </td>
            </tr>
            <tr>
                <th>状态</th>
                <td><?= ItemStatus::allStatus($item->item_status);?></td>
                <th>操作</th>
                <td>
                    <?php
                        $buttons = $item->buttons();
                        //去掉查看操作
                        array_shift($buttons);
                        $buttonList = '';
                        foreach($buttons as $button){
                            $attr = $button['attr'];
                            $class = '';
                            if(isset($attr['class'])){
                                $class = $attr['class'];
                            }
                            $data = '';
                            if(isset($attr['data'])){
                                $data = "data='".json_encode($attr['data'])."'";
                            }
                            $urlInfo =parse_url($button['url']);
                            if (Yii::$app->user->can($urlInfo['path'], isset($urlInfo['query']))?:[]) {
                                $buttonList.='<a href="'.$button['url'].'" '.$data.' class="btn btn-default '.$class.'">'.$button['label'].'</a>&nbsp;';
                            }
                        }

                        if(empty($buttonList)){
                            return '';
                        }
                        $output = '<div class="btn-groups">'.$buttonList.'</div>';
                        echo $output;
                    ?>
                </td>
            </tr>
            <tr>
                <th>缩略图</th>
                <?php
                    $output = '';
                    $files = $item->product->getFiles();
                    foreach($files as $file){
                        $img ='<img width="300" src="'.\renk\yiipal\helpers\FileHelper::getThumbnailPath($file->file_path, '300x300').'"/>';
                        $output .= '<a href="/'.$file->file_path.'" data-lightbox="image-'.$item->id.'" data-title="">'.$img.'</a>';
                    }
                ?>
                <td colspan="3"><?= $output;?></td>
            </tr>
        <?php endforeach;?>
    </table>
</div>
