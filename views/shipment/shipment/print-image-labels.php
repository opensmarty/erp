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
?>
<!DOCTYPE html>
<html>
<head>
    <style >
        @media print {
            @page {
                size: 8in 4in;
                width: 8in;
                height: 4in;
                margin:0;
                padding:0;
            }
            html,
            body{
                margin:0;
                padding:0;
            }
            div{
                margin:0;
                padding:0;
            }
            div>img{
                margin-top:0.05in;
                transform: rotate(180deg);
                width: 8in;
                height: 3.9in;
                border:none;
            }
            .noprint{display:none;}
        }
    </style>
</head>
<body style="margin: 0px;">
<p class="noprint" style="text-align: right;width: 1200px;"><span style="color: red;">打印选项：1. 页面边距选择【无】，2. 头部底部设置为不打印。 &nbsp;</span><button class="btn btn-primary" onclick="window.print();">打印</button></p>
<?php foreach($imageLabels as $imageLabel): ?>
    <div><img src="<?=$imageLabel;?>"/></div>
<?php endforeach;?>
</body>
</html>