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
$order = json_decode($model->order_info);
?>

<div class="order-view">
    <p>订单信息</p>
    <table class="table table-striped table-bordered detail-view">
        <tboday>
            <tr>
                <th>编号</th>
                <td><?= $order->ext_order_id;?></td>
                <th>订单号</th>
                <td><?= $order->increment_id;?></td>
            </tr>
            <tr>
                <th>总金额</th>
                <td><?= round($order->grand_total,2)."($order->currency_code)";?></td>
                <th>付款状态</th>
                <td><?= $order->payment_status;?></td>
            </tr>
            <tr>
                <th>创建时间</th>
                <td><?= date("Y-m-d H:i:s",$order->created_at);?></td>
                <th>付款方式</th>
                <td><?= $order->payment_method;?></td>
            </tr>
            <tr>
                <th>物流方式</th>
                <td><?= $order->shipping_method;?></td>
                <th>订单来源</th>
                <td><?= strtoupper($order->source);?></td>
            </tr>

        </tboday>
    </table>
    <p>收货信息</p>
    <table class="table table-striped table-bordered detail-view">
        <tr>
            <th>收货地址</th>
            <td><?= CommonHelper::filterEmptyStr($order->address->country_id) .' '. CommonHelper::filterEmptyStr($order->address->city).' '. CommonHelper::filterEmptyStr($order->address->region).' '.CommonHelper::filterEmptyStr($order->address->street);?>
                <?= CommonHelper::filterEmptyStr($order->address->company);?>
            </td>
        </tr>
        <tr>
            <th>收货人</th>
            <td><?= CommonHelper::filterEmptyStr($order->address->firstname) .' '. CommonHelper::filterEmptyStr($order->address->middlename).' '. CommonHelper::filterEmptyStr($order->address->lastname);?>
                <?= CommonHelper::filterEmptyStr($order->address->company);?>
            </td>
        </tr>
        <tr>
            <th>电话</th>
            <td><?= CommonHelper::filterEmptyStr($order->address->telephone);?>
            </td>
        </tr>
        <tr>
            <th>Email</th>
            <td><?= CommonHelper::filterEmptyStr($order->address->email);?>
            </td>
        </tr>
        <tr>
            <th>邮编</th>
            <td><?= CommonHelper::filterEmptyStr($order->address->postcode);?>
            </td>
        </tr>
    </table>

    <p>产品信息</p>
    <table class="table table-striped table-bordered detail-view">
        <?php foreach($order->items as $item): ?>
            <?php $product = \app\models\product\Product::findOne($item->product_id); ?>
            <tr class="first_tr">
                <th>SKU</th>
                <td>
                    <?= $item->sku;?>
                </td>
                <th>产品名称</th>
                <td><?= $product->name;?></td>
            </tr>
            <tr>
                <th>产品类型</th>
                <td><?= \app\helpers\Options::productTypes($product->type);?></td>
                <th>下单数量</th>
                <td>
                    <?= $item->qty_ordered;?>
                </td>
            </tr>
            <tr>
                <th>尺码</th>
                <td>
                    <?php if($item->size_type!= 'none') echo \app\helpers\Options::ringTypes($item->size_type); ?>
                    网站尺码：<?= $item->size_original;?> 实际尺码：<?=$item->size_us?>
                </td>
                <th>刻字</th>
                <td>
                    <?= $item->engravings;?>
                </td>
            </tr>
            <tr>
                <th>状态</th>
                <td><?= ItemStatus::allStatus($item->item_status);?></td>
                <th>操作</th>
                <td>无可用操作</td>
            </tr>
            <tr>
                <th>缩略图</th>
                <?php
                    $output = '';
                    $files = $product->getFiles();
                    $img ='<img width="300" src="'.\renk\yiipal\helpers\FileHelper::getThumbnailPath($files[0]->file_path, '300x300').'"/>';
                    $output .= '<a href="/'.$files[0]->file_path.'" data-lightbox="image-'.$item->id.'" data-title="">'.$img.'</a>';
                ?>
                <td colspan="3"><?= $output;?></td>
            </tr>
        <?php endforeach;?>
    </table>
</div>
