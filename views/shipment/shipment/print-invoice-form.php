<?php
/**
 * accept-request.php 
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/6/3
 */
use renk\yiipal\helpers\Html;
use renk\yiipal\helpers\Url;
use app\helpers\CommonHelper;
?>

<?php
function getItemsTotal($order){
    $total = 0;
    foreach($order->items as $item){
        if(in_array($item->item_status,['cancelled','shipped'])) continue;
        $total += $item->qty_ordered;
    }
    return $total;
}
?>
<!DOCTYPE html>
<html>
<head>
    <style >
        html,
        body{
            margin:0;
        }
        div{
            margin:0;
            padding:0;
        }
        table.outer-table{
            width: 210mm;
            margin: 0 auto;
            border-spacing: 0;
        }
        td{
            padding: 0;
            margin: 0;
        }
        td>div{
            padding: 1mm 1mm 0 1mm;
        }
        table.inner-table{
            width: 100%;
        }
        table.inner-table td{
            text-align: center;
        }
        table.invoice-info th{
            text-align: right;
        }
        table.invoice-info td{
            text-align: right;
        }
        @media print {
            @page {
                size: A4;
            }
            .noprint{display:none;}
        }
        .border{
            border: 0.3mm solid #000;
            border-bottom: 0;
        }
        .border-bottom{
            border-bottom: 0.3mm solid #000;
        }
        .margin-left-1{
            margin-left: 1mm;
            width: 102mm;
        }
        .margin-right-1{
            margin-right: 1mm;
            width: 102mm;
        }
        .bold{
            font-weight: bold;
        }
    </style>
</head>
<body style="margin: 0px;">
<p class="noprint" style="text-align: right;width: 1200px;"><span style="color: red;">打印选项：1. A4纸打印， 2. 页面边距选择【无】，3. 头部底部设置为不打印。 &nbsp;</span><button class="btn btn-primary" onclick="window.print();">打印</button></p>
<?php foreach($orders as $order): ?>
    <div class="form-wrapper">
        <div style="width: 210mm;margin: 0 auto;position: relative;"><h1 style="text-align: center;margin-bottom: 1mm;margin-top: 0;">Invoice</h1><span style="float: right;font-size: 14px;font-weight: bold;position: absolute;right: 0;bottom: 0;">Page 1</span></div>
        <table class="outer-table">
            <tr>
                <td><div class="border margin-right-1 bold" style="border-bottom: 0;">FORM</div></td>
                <td><div class="border margin-left-1" style="border-bottom: 0;">&nbsp;</div></td>
            </tr>
            <tr>
                <td>
                    <div class="border margin-right-1" >
                        <div class="bold">Tax ID/EIN/VAT No.:</div>
                        <div><span class="bold">Contact Name: </span>MR LIN</div>
                        <div>GZ JEULIA JEWELLERY CO.,LTD</div>
                        <div>SHATOU STREET YINJIAN RD 123#</div>
                        <div style="height: 45mm;line-height: 40mm;">
                            GUANGZHOU, GD 511400
                        </div>
                        <div>China (Peoples Republic)</div>
                        <div><span class="bold">Phone: </span>18003152361</div>
                    </div>
                </td>
                <td>
                    <div class="border margin-left-1">
                        <div><span class="bold">Waybill Number: </span><?=$order->shipping_track_no;?></div>
                        <div><span class="bold">Shipment ID: </span>1W508R8WYBL</div>
                        <div style="height: 15mm;text-align: center;">

                        </div>
                        <div><span class="bold">Date: </span><?=date("d/M/Y");?></div>
                        <div><span class="bold">Invoice No:</span></div>
                        <div><span class="bold">PO No:</span></div>
                        <div><span class="bold">Terms of Sale (Incoterm):</span></div>
                        <div><span class="bold">Reason for Export: </span>SALE</div>
                        <div style="height: 25mm;"></div>
                    </div>
                </td>
            </tr>
            <tr>
                <td><div class="border margin-right-1"><span class="bold">SHIP TO</span></div></td>
                <td><div class="border margin-left-1"><span class="bold">SOLD TO INFORMATION</span></div></td>
            </tr>
            <tr>
                <td>
                    <div class="border margin-right-1 border-bottom">
                        <div ><span class="bold">Tax ID/VAT No.:</span></div>
                        <?php $contactName = CommonHelper::filterEmptyStr($order->address->firstname).' '.CommonHelper::filterEmptyStr($order->address->lastname)?>
                        <div ><span class="bold">Contact Name: </span><?=$contactName?></div>
                        <div ><?=$contactName?></div>
                        <?php
                            $street = $order->address->street? str_replace(["\r\n", "\r", "\n"], ' ', $order->address->street) : '';
                            $street = str_replace(",",' ',$street);
                        ?>
                        <div style="height: 20mm;"><?=$street?></div>
                        <?php
                        $cityRegion = CommonHelper::filterEmptyStr($order->address->city).' '.CommonHelper::filterEmptyStr($order->address->region).' '.CommonHelper::filterEmptyStr($order->address->postcode);
                        ?>
                        <div style="height: 10mm;"><?=$cityRegion?></div>
                        <div>United States</div>
                        <div>Phone: <?=CommonHelper::filterEmptyStr($order->address->telephone)?></div>
                    </div>
                </td>
                <td>
                    <div class="border margin-left-1 border-bottom">
                        <div ><span class="bold">Tax ID/VAT No.:</span></div>
                        <div ><span class="bold">Contact Name: </span></div>
                        <div >Same as Ship To　</div>
                        <div style="height: 20mm;">&nbsp;</div>
                        <div style="height: 10mm;">&nbsp;</div>
                        <div>&nbsp;</div>
                        <div>&nbsp;</div>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <table class="inner-table border border-bottom" style="margin-top: 2mm;">
                        <tr><th>Units</th><th>U/M</th><th>Description of Goods/Part No.</th><th>Harm. Code</th><th>Harm. Code</th><th>Unit Value</th><th>Total Value</th></tr>
                        <?php $itemTotal = getItemsTotal($order); ?>
                        <tr><td><?=$itemTotal;?></td><td>PCS</td><td>ALLOY RING</td><td></td><td>CN</td><td>30.00</td><td><?=round($itemTotal*30,2);?></td></tr>
                        <tr><td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                    </table>
                </td>
            </tr>
            <tr><td colspan="2"><div style="height: 60mm;" class="border-bottom"></div></td></tr>
            <tr><td colspan="2"><div class="bold" style="height: 8mm;">Additional Comments:</div></td></tr>
            <tr>
                <td>
                    <div class="border margin-right-1 border-bottom">
                        <div class="bold">Declaration Statement:</div>
                        <div style="height: 43mm;">
                            I hereby certify that the information on this invoice is true and correct and the contents and value of this shipment is as stated above.
                        </div>
                        <div>
                            <table style="width: 100%;">
                                <tr>
                                    <td class="bold">Shipper</td>
                                    <td></td>
                                    <td class="bold">Date</td>
                                    <td></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="border margin-left-1 border-bottom" style="padding-left: 0px;padding-right: 0px;">
                        <table class="invoice-info" style="width: 100%;">
                            <tr>
                                <th>Invoice Line Total:</th>
                                <td></td>
                                <td></td>
                                <td><?=round($itemTotal*30,2);?></td>
                            </tr>
                            <tr>
                                <th>Discount/Rebate:</th>
                                <td></td>
                                <td></td>
                                <td>0.00</td>
                            </tr>
                            <tr>
                                <th>Invoice Sub-Total:</th>
                                <td></td>
                                <td></td>
                                <td><?=round($itemTotal*30,2);?></td>
                            </tr>
                            <tr>
                                <th>Freight:</th>
                                <td></td>
                                <td></td>
                                <td>0.00</td>
                            </tr>
                            <tr>
                                <th>Insurance:</th>
                                <td></td>
                                <td></td>
                                <td>0.00</td>
                            </tr>
                            <tr>
                                <th>Other:</th>
                                <td></td>
                                <td></td>
                                <td>0.00</td>

                            </tr>
                            <tr>
                                <th>Total Invoice Amount:</th>
                                <td></td>
                                <td></td>
                                <td><?=round($itemTotal*30,2);?></td>
                            </tr>
                            <tr><td colspan="4" style="border-top: 1px solid #000;"></td></tr>
                            <tr>
                                <th>Total Number of Packages: </th>
                                <td style="text-align: left;">1</td>
                                <td colspan="2" style="text-align: left;"><span class="bold">Currency: </span> USD</td>
                            </tr>
                            <tr>
                                <th>Total Weight:</th>
                                <td>0.5 KGS</td>
                                <td></td>
                                <td></td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>
    </div>
<?php endforeach;?>
</body>
</html>