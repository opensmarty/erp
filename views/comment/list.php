<?php
use renk\yiipal\helpers\Html;
use yii\helpers\Url;
use renk\yiipal\grid\GridView;
use renk\yiipal\helpers\FileHelper;
use yii\bootstrap\ActiveForm;
use app\helpers\ItemStatus;
/* @var $this yii\web\View */
function formatContent($content){
//    $content = str_replace('<img','<img style="max-width:260px;" ',$content);
    return $content;
}
?>
<div class="order-index">
    <div class="body-content content">
        <div class="row">
            <div class="col-xs-12">
                <table class="table table-striped table-bordered">
                    <thead>
                    <tr>
                        <th style="width: 45px;">编号</th>
                        <th style="max-width: 260px;">备注内容</th>
                        <th style="width: 115px;">补充信息</th>
                        <th style="width: 85px;">备注时间</th>
                        <th style="width: 75px;">备注人</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach($comments as $index=> $comment): ?>
                        <tr>
                            <td><?=count($comments)-$index;?></td>
                            <td><div style="max-width: 260px; overflow: auto;"><?php echo $comment->content;?></div></td>
                            <td><?= \app\helpers\Options::commentTypes($comment->subject);?></td>
                            <td><?= date('Y-m-d H:i:s',$comment->created_at)?></td>
                            <td><?=$comment->uid<0?"系统API":$comment->user->nick_name;?></td>
                        </tr>
                    <?php endforeach;?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
