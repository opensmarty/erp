<?php
use yii\helpers\Html;
use yii\widgets\Pjax;
app\assets\AppAsset::register($this);
dmstr\web\AdminLteAsset::register($this);

$directoryAsset = Yii::$app->assetManager->getPublishedUrl('@vendor/almasaeed2010/adminlte/dist');
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?=  Yii::$app->name; ?></title>
    <?php $this->head() ?>
</head>
<body class="hold-transition sidebar-mini  <?php echo \dmstr\helpers\AdminLteHelper::skinClass() ?>  <?php echo !isset($_COOKIE['sidebar-collapse']) || $_COOKIE['sidebar-collapse']==1?'sidebar-collapse':''; ?>">
<?php $this->beginBody() ?>
<div class="wrapper">

    <?= $this->render(
        'header.php',
        ['directoryAsset' => $directoryAsset]
    ) ?>

    <?= $this->render(
        'left.php',
        ['directoryAsset' => $directoryAsset]
    )
    ?>

    <?= $this->render(
        'content.php',
        ['content' => $content, 'directoryAsset' => $directoryAsset]
    ) ?>

</div>
<?php $this->endBody() ?>

<div class="modal fade" id="global-ajax-modal" tabindex="-1" role="dialog" aria-labelledby="global-ajax-modal-label">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="exampleModalLabel">备注</h4>
            </div>
            <div class="modal-content-body">
                <p>加载中...</p>
            </div>
        </div>
    </div>
</div>
</body>
</html>
<?php $this->endPage() ?>
