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
function getDigitCode($code){
    $digitCode = [
        "0"=>"3",
        "1"=>"4",
        "2"=>"7",
        "3"=>"8",
        "4"=>"9",
        "5"=>"B",
        "6"=>"C",
        "7"=>"D",
        "8"=>"F",
        "9"=>"G",
        "10"=>"H",
        "11"=>"J",
        "12"=>"K",
        "13"=>"L",
        "14"=>"M",
        "15"=>"N",
        "16"=>"P",
        "17"=>"Q",
        "18"=>"R",
        "19"=>"S",
        "20"=>"T",
        "21"=>"V",
        "22"=>"W",
        "23"=>"X",
        "24"=>"Y",
        "25"=>"Z"
    ];
    return isset($digitCode[$code])?$digitCode[$code]:'';
}

function getItemsTotal($order){
    $total = 0;
    foreach($order->items as $item){
        if(in_array($item->item_status,['cancelled','shipped'])) continue;
        $total += $item->qty_ordered;
    }
    return $total;
}

function generatePackageId($trackNo){
    $trackNo = substr($trackNo,10,7);
    //$trackNo = 2486290;
    $a =  floor($trackNo/pow(26,4));
    $b = floor(($trackNo-($a*pow(26,4)))/pow(26,3));
    $c = floor(($trackNo-($a*pow(26,4))-($b*pow(26,3)))/pow(26,2));
    $d = floor(($trackNo-$a*pow(26,4)-$b*pow(26,3)-$c*pow(26,2))/26);
    $e = floor(($trackNo-$a*pow(26,4)-$b*pow(26,3)-$c*pow(26,2) -$d*26));
    $packageId = '1W508R'.getDigitCode($a).getDigitCode($b).getDigitCode($c).getDigitCode($d).getDigitCode($e);
    return $packageId;
}
?>
<!DOCTYPE html>
<html>
<head>
    <style >
        html,
        body{
            margin:0;
            font-size: 12px;
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
        table.outer-table>tbody>tr>td{
            width: 31%;
            border: 0.3mm solid #000;
            vertical-align: top;
        }
        table.outer-table>tbody>tr>td:first-child{
            width: 37%;
            border-right: none;
        }
        table.outer-table>tbody>tr>td:last-child{
            width: 32%;
            border-left: none;
        }
        .td-bold{
            height: 5mm;
            line-height: 5mm;
            background-color: #000;
            color: #FFF;
            font-weight: bold;
            /*font-size: 1em;*/
        }
        td{
            padding: 0;
            margin: 0;
        }
        td>div{
            /*padding: 1mm 1mm 0 1mm;*/
        }
        table,
        table.inner-table{
            width: 100%;
        }

        table.shipment-info td{
            text-align: center;
            width: 30%;
        }

        table.shipment-info td:first-child{
            text-align: right;
        }

        table.shipment-info td:last-child{
            text-align: left;
        }

        div.item{
            padding: 0.5mm 1mm 0.5mm 1mm;
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
            .td-bold{
                background: #000;
            }
            div.form-wrapper{
                page-break-after:always;
            }
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
<p class="noprint" style="text-align: right;width: 1200px;"><span style="color: red;">打印选项：1. A4纸打印， 2. 页面边距选择【无】，3. 头部底部设置为不打印。4. 打印背景设置为打印。 &nbsp;</span><button class="btn btn-primary" onclick="window.print();">打印</button></p>
<?php foreach($orders as $order): ?>
    <div class="form-wrapper">
        <div style="width: 210mm;margin: 0 auto;position: relative;"><h1 style="text-align: center;margin-bottom: 1mm;margin-top: 0;font-weight: normal;">UPS COPY</h1><span style="float: right;font-size: 2em;font-weight: bold;position: absolute;right: 45mm;bottom: 0;">EDI</span></div>
        <table class="outer-table">
            <tr>
                <td class="td-bold">SHIPPER</td>
                <td style="border-top: 0;border-bottom: 0;"></td>
                <td class="td-bold">UPS WAYBILL/TRACKING NUMBER</td>
            </tr>
            <tr>
                <td class="" style="border-bottom: 0;">
                    <div>
                        <div class="item bold">UPS Account Number: 1W508R</div>
                        <div class="item bold">Tax ID/VAT No.:</div>
                        <div class="item "><span class="bold">Contact:</span> MR LIN</div>
                        <div class="item ">GZ JEULIA JEWELLERY CO.,LTD</div>
                        <div class="item"><span class="bold">Phone:</span> 18003152361</div>
                        <div class="item">SHATOU STREET YINJIAN RD 123#</div>
                        <div class="item">3# BUILDING 1ST FLO</div>
                        <div class="item">GUANGZHOU GD</div>
                        <div class="item">511400</div>
                        <div class="item bold">CHINA, PEOPLE'S REPUBLIC OF </div>
                        <div class="item bold">CN </div>
                        <div style="height: 5mm;"></div>
                        <div class="item td-bold">SHIP TO</div>
                        <div class="item bold">UPS Account Number:</div>
                        <div class="item bold">Tax ID/VAT No.:</div>
                        <?php $contactName = CommonHelper::filterEmptyStr($order->address->firstname).' '.CommonHelper::filterEmptyStr($order->address->lastname)?>
                        <div class="item"><span class="bold">Contact:</span> <?=$contactName;?></div>
                        <div class="item"><?=$contactName;?></div>
                        <div class="item"><span class="bold">Phone:</span> <?=CommonHelper::filterEmptyStr($order->address->telephone)?></div>
                        <?php
                        $street = $order->address->street? str_replace(["\r\n", "\r", "\n"], ' ', $order->address->street) : '';
                        $street = str_replace(",",' ',$street);
                        ?>
                        <div class="item"><?=$street?></div>
                        <?php
                        $cityRegion = CommonHelper::filterEmptyStr($order->address->city).' '.CommonHelper::filterEmptyStr($order->address->region).' '.CommonHelper::filterEmptyStr($order->address->postcode);
                        ?>
                        <div class="item"><?=$cityRegion?></div>
                        <?php
                        $state = '';
                        if(!empty($order->address->region)){
                            $state = CommonHelper::getRegionShortName($order->address->region);
                        }
                        $cityRegion = CommonHelper::filterEmptyStr($order->address->city).' '.$state;
                        ?>
                        <div class="item"><?=$cityRegion?></div>
                        <div class="item bold">United States</div>
                        <div class="item bold">US</div>
                    </div>
                </td>
                <td >
                    <div>
                        <div class="item">EXPRESS SAVER  <span style="display: inline-block;width: 50%;"></span>1P</div>
                        <div class="item td-bold">SHIPMENT INFORMATION</div>
                        <div class="item">
                            <table class="shipment-info">
                                <tr>
                                    <td>Pkgs</td>
                                    <td>1</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>Lg. Pkgs.</td>
                                    <td>0</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>Actual Wt</td>
                                    <td>0.5</td>
                                    <td>Kg</td>
                                </tr>
                                <tr>
                                    <td>Billable Wt</td>
                                    <td>0.5</td>
                                    <td>Kg</td>
                                </tr>
                            </table>
                        </div>
                        <div style="height: 20mm;"></div>
                        <div class="item">
                            Description of Goods:
                            <div>ALLOY RING</div>
                        </div>
                        <div class="item">Declared Value for Carriage:</div>
                        <div style="height: 10mm;"></div>
                        <div class="item">Additional Handling:</div>
                        <div class="item">Residential: Yes</div>
                        <div class="item">Reference 1: <?=$order->ext_order_id;?></div>
                        <div class="item">Reference 2: </div>
                        <div style="height: 23.5mm;"></div>
                        <div class="item td-bold">CARRIER USE</div>
                        <div class="item border-bottom">Received For UPS By:</div>
                        <div class="item border-bottom">&nbsp;</div>
                        <div class="item">Amount Received:</div>
                    </div>
                </td>
                <td class="">
                    <div>
                        <div class="item"><?=$order->shipping_track_no;?></div>
                        <div class="item td-bold">UPS SHIPMENT ID</div>
                        <div class="item bold"><?=generatePackageId($order->shipping_track_no);?></div>
                        <div class="item td-bold">SPECIAL INSTRUCTIONS</div>
                        <div class="item">[X] Package</div>
                        <div style="height: 50mm;"></div>
                        <div class="item td-bold">PAYMENT OF CHARGES</div>
                        <div class="item">[X] PRE</div>
                        <div class="item">[X] Bill Transportation to Shipper 1W508R</div>
                        <div class="item">[X] Bill Duty and Tax to Receiver</div>
                        <div style="height: 20mm;"></div>
                        <div class="item td-bold"></div>
                        <div class="item border-bottom">
                            <div style="display: inline-block;width: 50%;text-align: center;margin-right:-1px;border-right: 1px solid #000;">Date</div><div style="text-align: center;display: inline-block;width: 50%;">Time</div>
                        </div>
                        <div class="item border-bottom">&nbsp;</div>
                        <div class="item"><div style="display: inline-block;width: 50%;text-align: center;">[ ] Cheque</div><div style="text-align: center;display: inline-block;width: 50%;">[ ] Cash</div></div>
                    </div>
                </td>
            </tr>
            <tr><td style="border-top: 0;border-bottom: 0;"></td><td colspan="2" style="border-top: 0;border-left: 0.3mm solid #000;"><div class="item">No. of packages for which the Additional Handling charge applies:</div></td></tr>
            <tr><td style="border-top: 0;"></td><td colspan="2" style="border-top: 0;border-left: 0.3mm solid #000;"><div class="item">Other Information:</div></td></tr>
        </table>
    </div>
<?php endforeach;?>
</body>
</html>