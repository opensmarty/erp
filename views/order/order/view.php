<?php
use renk\yiipal\helpers\Html;
use yii\widgets\DetailView;
use app\helpers\ItemStatus;
use renk\yiipal\helpers\StrHelper;
use app\helpers\CommonHelper;
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
    if(!in_array($model->order->payment_status,['processing','complete'])||in_array($model->item_status,['cancelled','shipped','complete','return_completed_part','return_completed']) || in_array($model->order->status,['cancelled','exchange_process','exchange_process_part'])){
        return false;
    }else{
        return true;
    }
}
//检查Order编辑条件
function canEditOrder($model){
    if(!in_array($model->payment_status,['processing','complete'])||in_array($model->status,['cancelled','shipped','complete','return_completed','exchange_process','exchange_process_part'])){
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
                <td><?= $model->ext_order_id;?></td>
                <th>订单号</th>
                <td><?= $model->increment_id;?></td>
            </tr>
            <tr>
                <th>总金额</th>
                <td><?= round($model->grand_total,2)."($model->currency_code)";?>
                    <?php if(canEditOrder($model)) echo Html::authLinkWithComment('','/comment/create?target_id='.$model->id.'&type=order&subject='.\app\models\Comment::COMMENT_TYPE_OTHERS,'/order/order/edit-order-total?id='.$model->id);?>
                </td>
                <th>付款状态</th>
                <td><?= $model->payment_status;?></td>
            </tr>
            <tr>
                <th>创建时间</th>
                <td><?= date("Y-m-d H:i:s",$model->created_at);?></td>
                <th>付款方式</th>
                <td><?= $model->payment_method;?></td>
            </tr>
            <tr>
                <th>物流方式</th>
                <td><?= $model->shipping_method;?> <?php if(canEditOrder($model)) echo Html::authLinkWithComment('','/comment/create?target_id='.$model->id.'&type=order&subject='.\app\models\Comment::COMMENT_TYPE_CHANGE_SHIPPING_METHOD,'/order/order/edit-shipping-method?id='.$model->id);?></td>
                <th>订单来源</th>
                <td><?= strtoupper($model->source);?></td>
            </tr>

        </tboday>
    </table>
    <p>收货信息
        <?php if(!in_array($model->status,['cancelled','shipped','complete','return_completed'])) echo Html::authLinkWithComment('','/comment/create?target_id='.$model->id.'&type=order&subject='.\app\models\Comment::COMMENT_TYPE_CHANGE_ADDRESS,'/order/order/edit-shipping-address?id='.$model->id);?>
        <?php if($orderIssue): ?>
            | <a class="text-danger text-bold" href="/order/issue/index?Order[increment_id]=<?=$model->increment_id?>">该订单需要联系客户</a>
        <?php endif;?>
    </p>
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
            <td><?= CommonHelper::filterEmptyStr($model->address->telephone);?>
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
                    <?= $item->qty_ordered;?>
                    <?php if(canEditItem($item)) echo Html::authLinkWithComment('','/comment/create?target_id='.$model->id.'&type=order&subject='.\app\models\Comment::COMMENT_TYPE_CHANGE_PRODUCT_NUMBER,'/order/order/edit-order-quantity?id='.$item->id);?>
                </td>
            </tr>
            <tr>
                <th>尺码</th>
                <td>
                    <?php if($item->size_type!= 'none') echo \app\helpers\Options::ringTypes($item->size_type); ?>
                    网站尺码：<?= $item->product->cid==3?$item->size_original:'无'?> 实际尺码：<?=$item->product->cid==3?$item->size_us:'无'?>
                    <?php if(canEditItem($item)) echo Html::authLinkWithComment('','/comment/create?target_id='.$model->id.'&type=order&subject='.\app\models\Comment::COMMENT_TYPE_CHANGE_PRODUCT_SIZE,'/order/order/edit-order-product-size?id='.$item->id);?>
                </td>
                <th>刻字</th>
                <td>
                    <?= $item->engravings?>
                    <?php if(canEditItem($item)) echo Html::authLinkWithComment('','/comment/create?target_id='.$model->id.'&type=order&subject='.\app\models\Comment::COMMENT_TYPE_CHANGE_PRODUCT_ENGRAVINGS,'/order/order/edit-order-engravings?id='.$item->id);?>
                </td>
            </tr>
            <tr>
                <th>状态</th>
                <td><?= ItemStatus::allStatus($item->item_status);?></td>
                <th>操作</th>
                <td><?php if(canEditItem($item)) echo Html::authLinkWithComment('取消','/comment/create?target_id='.$model->id.'&type=order&subject='.\app\models\Comment::COMMENT_TYPE_ORDER_CANCEL,'/order/order/cancel-product?id='.$item->id,['class'=>'btn btn-danger btn-sm'],false); else echo '无可用操作';?></td>
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
    <?php if($model->histories): ?>
    <p>订单历史</p>
    <table class="table table-striped">
        <?php foreach($model->histories as $row): ?>
        <tr>
            <td>
                <span class="user-name"><?=$row->user->nick_name?></span>
            </td>
            <td>于</td>
            <td><?= date("Y-m-d H:i:s",$row->created_at);?></td>
            <td>
                对订单编号
            </td>
            <td>
                <span class="order-id"><?=Html::a($row->ext_order_id,\renk\yiipal\helpers\Url::to(['/order/order/view-history','id'=>$row->order_id]))?></span>
            </td>
            <td>
                进行了
            </td>
            <td>
                <span class="order-operation text-danger"><?=$row->operation?></span>
            </td>
            <td>
                 的操作
            </td>
        </tr>
        <?php endforeach;?>
    </table>
    <?php endif;?>
</div>
