<?php
use renk\yiipal\helpers\Html;
use yii\helpers\Url;
use renk\yiipal\grid\GridView;
use yii\bootstrap\ActiveForm;
/* @var $this yii\web\View */
//$this->title = '产品列表';
?>

<?php 

$css = <<<CSS
.product_is_clean{
    display:inline-block;
    padding:10px;
}

CSS;

$this->registerCss($css);
?>

<div class="product-index">
    <div class="body-content">
        <div class="row btn-group-top">
            <div class="col-xs-12">
                <div class="btn-group pull-right">
                    <div class="btn-group-top">
                        <?= Html::authLink('新增', ['create'], ['class' => 'btn btn-success']) ?>
                        <?= Html::authSubmitButton('导出', ['export-stocks'], ['class' => 'btn btn-default download','form-class'=>'ajax-form download', 'data-action-before'=>'get_ids']) ?>
                    </div>
                </div>
            </div>
            <div class="col-xs-6">
            </div>
        </div>
        <div class="row">
            <div class="col-lg-3 col-xs-6">
                <!-- small box -->
                <div class="small-box bg-green">
                    <div class="inner">
                        <h3><?=intval($stockInfo['stocksTotal']);?></h3>

                        <p>实际库存</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-bag"></i>
                    </div>
                </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-xs-6">
                <!-- small box -->
                <div class="small-box bg-aqua">
                    <div class="inner">
                        <h3><?=intval($stockInfo['virtualTotal']);?></h3>

                        <p>在补库存</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-stats-bars"></i>
                    </div>
                </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-xs-6">
                <!-- small box -->
                <div class="small-box bg-yellow">
                    <div class="inner">
                        <h3><?=round($stockInfo['stocksTotal']+$stockInfo['virtualTotal']);?></h3>

                        <p>库存合计</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-person-add"></i>
                    </div>
                </div>
            </div>
            <!-- ./col -->
        </div>
        <div class="row">
            <div class="col-xs-12">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'columns' => [
                        [
                            'class' => 'yii\grid\CheckboxColumn',
                            'checkboxOptions' => function ($model, $key, $index, $column) {
                                return ['value' => $model->id];
                            }
                        ],
                        'id',
                        'sku',
                        [
                            'class' => 'yii\grid\DataColumn',
                            'contentOptions'=>['style'=>'max-width: 80px;padding:0px;'],
                            'label' => '图片',
                            'attribute' => 'file',
                            'value' => function ($data) {
                                $image = $data->getMasterImage();
                                return \renk\yiipal\helpers\FileHelper::getThumbnailWithLink($image,$data->id);
                            },
                            'format'=>'raw',
                            'enableSorting'=>false,
                        ],
                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '类型',
                            'attribute' => 'type',
                            'filter'=>function($data){
                                return $data->getTypeOptions();
                            },
                            'value' => function ($data) {
                                return $data::getTypeLabel($data->type);
                            },
                            'format'=>'raw',
                            'enableSorting'=>true,
                        ],
                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '产品类型',
                            'attribute' => 'cid',
                            'contentOptions'=>['style'=>'max-width: 100px;min-width: 100px;'],
                            'filter'=>function($data){
                                return ['rings'=>'戒指','ring_single'=>' |--单戒','ring_couple'=>' |--对戒','ring_set'=>' |--套戒','necklace'=>'项链','bracelet'=>'手链','earrings'=>'耳环'];
                            },
                            'value' => function ($data) {
                                $output = '';
                                switch($data->cid){
                                    case 4:
                                        $output = '项链';
                                        break;
                                    case 5:
                                        $output = '手链';
                                        break;
                                    case 25:
                                        $output = '耳环';
                                        break;
                                    case 3:
                                        if($data->is_couple == 1) {
                                            $output = '戒指-对戒';
                                        }else if($data->is_couple == 2){
                                            $output = '戒指-套戒';
                                        }else{
                                            $output = '戒指-单戒';
                                        }
                                        break;
                                }
                                if($data->type == 'virtual'){
                                    $output = '';
                                }
                                return $output;
                            },
                            'format'=>'raw',
                            'enableSorting'=>true,
                        ],
                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '库存',
                            'attribute' => 'stock_total',
                            'filter'=>['-2'=>'没有库存','-1'=>'有库存','1-10'=>'1-10','10-50'=>'10-50','50-100'=>'50-100','100-1000'=>'100-1000','1000-10000'=>'1000-10000'],
                            'value' => function ($data) {
                                if(empty($data->stocksInfo)){
                                    return '<span class="item">没有库存信息</span>';
                                }
                                $output = '<ul class="list-group" style="overflow-y: auto;max-height: 200px;">';
                                $typeFlag = '';
                                foreach($data->stocksInfo as $item){
                                    $type = '';
                                    if($item['type']!='none'){
                                        $type = \app\helpers\Options::ringTypes($item['type']);
                                    }
                                    if($typeFlag != $type){
                                        $output .= '<li class="list-group-item list-group-item-info">'.$type.'</li>';
                                        $typeFlag = $type;
                                    }

                                    $size = '';
                                    if(!empty($item['size_code'])){
                                        $size = $item['size_code'].': ';
                                    }
                                    $total = intval($item['actual_total'])+intval($item['virtual_total']);
                                    $output .= '<li class="list-group-item">'.$size.$total.'(实际：'.intval($item['actual_total']).' 在补：'.intval($item['virtual_total']).')'.'</li>';
                                }
                                $output.='</ul>';
                                return $output;
                            },
                            'format'=>'raw',
                            'enableSorting'=>false,
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '在补',
                            'attribute' => 'virtual_total',
                            'filter'=>['1'=>'有','0'=>'无'],
                            'value' => function ($data) {
                                if(empty($data->stocksInfo)){
                                    return '<span class="item">没有库存信息</span>';
                                }
                                $output = '<ul class="list-group" style="overflow-y: auto;max-height: 200px;">';
                                $typeFlag = '';
                                foreach($data->stocksInfo as $item){
                                    $type = '';
                                    if($item['type']!='none'){
                                        $type = \app\helpers\Options::ringTypes($item['type']);
                                    }
                                    if($typeFlag != $type){
                                        $output .= '<li class="list-group-item list-group-item-info">'.$type.'</li>';
                                        $typeFlag = $type;
                                    }

                                    $size = '';
                                    if(!empty($item['size_code'])){
                                        $size = $item['size_code'].': ';
                                    }
                                    $total = intval($item['actual_total'])+intval($item['virtual_total']);
                                    $output .= '<li class="list-group-item">'.' 在补：'.intval($item['virtual_total']).'</li>';
                                }
                                $output.='</ul>';
                                return $output;
                            },
                            'format'=>'raw',
                            'enableSorting'=>true,
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'attribute' => 'is_clean',
                            'contentOptions'=>['style'=>'max-width: 50px;min-width: 50px;'],
                            'filter'=>function($data){
                                return ['0'=>'否','1'=>'是'];
                            },
                            'value' => function ($data) {
                                $output = '';
                                
                                $labelDanger = '';
                                if($data->is_clean == 1){
                                    $labelDanger = 'label-danger';
                                }
                                
                                $url = '/product/product/edit-is-clean';
                                if(Yii::$app->user->can(Url::to($url))){
                                    $output .='<span class="item '.$labelDanger.' product_is_clean"><a href="#" data-name="is_clean" class="edit-is-clean" data-type="select" data-pk="'.$data->id.'" data-url="'.$url.'" data-title="是否清仓">'.($data->is_clean == 1 ? '是':'否').'<i class="glyphicon glyphicon-pencil"></i></a></span>';
                                }else{
                                    $output = '<span class="item '.$labelDanger.' product_is_clean">'.($data->is_clean == 1 ? '是':'否').'</span>';
                                }
                                return $output;
                            },
                            'format'=>'raw',
                            'enableSorting'=>true,
                        ],
                        [
                            'class' => 'renk\yiipal\grid\ActionColumn',
                            'contentOptions'=>['style'=>'width: 85px;'],
                        ],
                    ],
                ]); ?>
            </div>
        </div>

    </div>
</div>
