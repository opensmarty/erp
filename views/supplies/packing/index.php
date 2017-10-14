<?php
use renk\yiipal\helpers\Html;
use yii\helpers\Url;
use renk\yiipal\grid\GridView;
use yii\bootstrap\ActiveForm;
/* @var $this yii\web\View */
//$this->title = '产品列表';
?>
<?php
function renderDataCellContent($model)
{
    $buttons = $model->buttons();

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
            $buttonList.='<li><a href="'.$button['url'].'" '.$data.' class="'.$class.'">'.$button['label'].'</a></li>';
        }
    }

    if(empty($buttonList)){
        return '';
    }
    $output = '<div class="btn-group">
                      <button type="button" class="btn btn-default btn-xs btn-flat ">操作</button>
                      <button type="button" class="btn btn-default btn-xs btn-flat dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                        <span class="caret"></span>
                        <span class="sr-only">切换</span>
                      </button>
                      <ul class="dropdown-menu" role="menu">
                        '.$buttonList.'
                      </ul>
                </div>';
    return $output;
}
?>
<div class="order-index">
    <div class="body-content">
        <div class="row btn-group-top">
            <div class="col-xs-12">
                <div class="btn-group pull-right">
                    <div class="btn-group-top">
                        <?= Html::authLink('进货', ['create'], ['class' => 'btn btn-success']) ?>
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
                        <h3><?=intval($costInfo['paid']+$costInfo['unpaid']);?></h3>

                        <p>总金额</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-bag"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-xs-6">
                <!-- small box -->
                <div class="small-box bg-aqua">
                    <div class="inner">
                        <h3><?=intval($costInfo['paid']);?></h3>

                        <p>已支出</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-bag"></i>
                    </div>
                </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-xs-6">
                <!-- small box -->
                <div class="small-box bg-yellow">
                    <div class="inner">
                        <h3><?=intval($costInfo['unpaid']);?></h3>

                        <p>待支付</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-stats-bars"></i>
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
//                        ['class' => 'yii\grid\SerialColumn'],
                        [
                            'class' => 'yii\grid\DataColumn',
                            'contentOptions'=>['style'=>'max-width: 100px;min-width: 100px;width:100px;'],
                            'filterOptions'=>['style'=>'max-width: 100px;min-width: 100px;width:100px;'],
                            'label' => '批次',
                            'attribute' => 'batch_number',
                            'value' => function ($data) {
                                return $data->batch_number;
                            },
                            'format'=>'raw',
                        ],
                        [
                            'class' => 'yii\grid\DataColumn',
                            'contentOptions'=>['style'=>'max-width: 100px;min-width: 100px;width:100px;'],
                            'filterOptions'=>['style'=>'max-width: 100px;min-width: 100px;width:100px;'],
                            'label' => '状态',
                            'attribute' => 'status',
                            'filter'=>['pending'=>'进货中','finished'=>'已到货'],
                            'value' => function ($data) {
                                $options = ['pending'=>'进货中','finished'=>'已到货'];
                                return $options[$data->status];
                            },
                            'format'=>'raw',
                        ],

                        [
                            'class' => 'renk\yiipal\grid\DataColumn',
                            'contentOptions'=>['style'=>'max-width: 200px;min-width: 200px;width:200px;'],
                            'filterOptions'=>['style'=>'max-width: 200px;min-width: 200px;width:200px;'],
                            'label' => '进货时间',
                            'attribute' => 'created_at',
                            'filter'=>function($model){
                                $gets = Yii::$app->request->get('PackingGroup',[]);
                                $created_at = isset($gets['created_at'])?$gets['created_at']:'';
                                $output = '<input type="text" class="form-control form-daterange pull-left" name="PackingGroup[created_at]" value="'.$created_at.'"/>';
                                return $output;
                            },
                            'value' => function ($data) {
                                return date("Y-m-d H:i",$data->created_at);
                            },
                            'format'=>'raw',
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'contentOptions'=>['style'=>'max-width: 100px;min-width: 100px;width:100px;'],
                            'label' => '总金额',
                            'attribute' => 'foo',
                            'value' => function ($data) {
                                $items = $data->packing;
                                $output = 0;
                                foreach($items as $item){
                                    $output+=($item->price*$item->qty);
                                }
                                return $output;
                            },
                            'format'=>'raw',
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'contentOptions'=>['style'=>'max-width: 100px;min-width: 100px;width:100px;'],
                            'label' => '已支付',
                            'attribute' => 'foo',
                            'value' => function ($data) {
                                return $data->paid;
                            },
                            'format'=>'raw',
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'contentOptions'=>['style'=>'max-width: 100px;min-width: 100px;width:100px;'],
                            'label' => '未支付',
                            'attribute' => 'foo',
                            'value' => function ($data) {
                                $items = $data->packing;
                                $output = 0;
                                foreach($items as $item){
                                    $output+=($item->price*$item->qty);
                                }
                                $output = round($output,2);
                                return round($output-$data->paid,2);
                            },
                            'format'=>'raw',
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'contentOptions'=>['style'=>'max-width: 100px;min-width: 100px;'],
                            'label' => '名称',
                            'filter'=>$materialOptions,
                            'attribute' => 'material_id',
                            'value' => function ($data) {
                                $items = $data->packing;
                                $output = '';
                                foreach($items as $item){
                                    $output.='<span class="item">'.$item->name.'</span>';
                                }
                                return $output;
                            },
                            'format'=>'raw',
                        ],
                        [
                            'class' => 'yii\grid\DataColumn',
                            'contentOptions'=>['style'=>'max-width: 100px;min-width: 100px;'],
                            'label' => '单价',
                            'attribute' => 'price',
                            'value' => function ($data) {
                                $items = $data->packing;
                                $output = '';
                                foreach($items as $item){
                                    $output.='<span class="item">'.$item->price.'</span>';
                                }
                                return $output;
                            },
                            'format'=>'raw',
                            'filter'=>false,
                        ],
                        [
                            'class' => 'yii\grid\DataColumn',
                            'contentOptions'=>['style'=>'max-width: 100px;min-width: 100px;'],
                            'label' => '数量',
                            'attribute' => 'qty',
                            'value' => function ($data) {
                                $items = $data->packing;
                                $output = '';
                                foreach($items as $item){
                                    $output.='<span class="item">'.$item->qty.'</span>';
                                }
                                return $output;
                            },
                            'format'=>'raw',
                            'filter'=>false,
                        ],
                        [
                            'class' => 'yii\grid\DataColumn',
                            'contentOptions'=>['style'=>'max-width: 100px;min-width: 100px;'],
                            'label' => '到货数量',
                            'attribute' => 'qty_delivered',
                            'value' => function ($data) {
                                $items = $data->packing;
                                $output = '';
                                foreach($items as $item){
                                    $output.='<span class="item">'.$item->qty_delivered.'</span>';
                                }
                                return $output;
                            },
                            'format'=>'raw',
                            'filter'=>false,
                        ],
                        [
                            'class' => 'yii\grid\DataColumn',
                            'contentOptions'=>['style'=>'max-width: 100px;min-width: 100px;'],
                            'label' => '图片',
                            'attribute' => 'file',
                            'value' => function ($data) {
                                $items = $data->packing;
                                $output = '';
                                foreach($items as $item){
                                    $img =\renk\yiipal\helpers\FileHelper::getThumbnailWithLink($item->material->getMasterImage(),$item->id);
                                    $output.='<span class="item">'.$img.'</span>';
                                }
                                return $output;
                            },
                            'format'=>'raw',
                            'enableSorting'=>false,
                        ],
                        [
                            'class' => 'renk\yiipal\grid\ActionColumn',
                            'contentOptions'=>['style'=>'min-width: 75px;'],
                            'customButtons' => function($model, $key, $index){
                                $output = '';
                                $items = $model->packing;
                                foreach($items as $item){
                                    $button = renderDataCellContent($item);
                                    $output .= '<span class="item">'.$button.'</span>';
                                }
                                return $output;
                            },
                        ],
                    ],
                ]); ?>
            </div>
        </div>

    </div>
</div>
