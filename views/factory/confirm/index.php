<?php
use renk\yiipal\helpers\Html;
use yii\helpers\Url;
use renk\yiipal\grid\GridView;
use renk\yiipal\helpers\FileHelper;
use yii\bootstrap\ActiveForm;
use app\helpers\ItemStatus;
/* @var $this yii\web\View */

?>
<div class="order-index">
    <div class="body-content">
        <div class="row btn-group-top">
            <div class="col-xs-12">
                <div class="btn-group pull-right">
                    <div class="btn-group-top">
                        <?= Html::authSubmitButton('导出', ['export'], ['class' => 'btn btn-default download','form-class'=>'ajax-form download', 'data-action-before'=>'get_ids']) ?>
                        <?= Html::authSubmitButton('确认变更', ['change-confirm'], ['class' => 'btn btn-primary confirm','form-class'=>'ajax-form', 'data-action-before'=>'get_ids']) ?>
                    </div>
                </div>
            </div>
            <div class="col-xs-6">
            </div>
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
                            },
                            'contentOptions'=>['class'=>'checkbox-column'],
                        ],
                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '编号',
                            'attribute' => 'ext_order_id',
                            'value' => function ($data) {
                                return '<span class="item">'.$data->ext_order_id.'</span>';;
                            },
                            'format' => 'raw',
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '加急',
                            'contentOptions'=>['style'=>'max-width: 65px;'],
                            'filter'=>function($data){
                                return ['1'=>'是','0'=>'否'];
                            },
                            'attribute' => 'expedited',
                            'value' => function ($data) {
                                $output= '否';
                                if($data->expedited == 1){
                                    $output = '<small class="label label-danger"><i class="fa fa-clock-o"></i>加急</small>';
                                }
                                return '<span class="item">'.$output.'</span>';
                            },
                            'format' => 'raw',
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => 'SKU',
                            'attribute' => 'sku',
                            'contentOptions'=>['style'=>'min-width: 100px;'],
                            'value' => function ($data) {
                                $items = $data->items;
                                $output = '';
                                foreach($items as $item){
                                    if(empty($item->last_item_info)){
                                        continue;
                                    }
                                    $output.='<span class="item">'.$item->sku.'</span>';
                                }
                                return $output;
                            },
                            'format' => 'raw',
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'contentOptions'=>['style'=>'max-width: 80px;padding:0px;'],
                            'label' => '图片',
                            'attribute' => 'file',
                            'value' => function ($data) {
                                $items = $data->items;
                                $output = '';
                                $products = $data->products;
                                foreach($items as $item){
                                    if(empty($item->last_item_info)){
                                        continue;
                                    }
                                    $image = $products[$item->product_id]->getMasterImage();
                                    $output .=\renk\yiipal\helpers\FileHelper::getThumbnailWithLink($image,$item->id);
                                }
                                return $output;
                            },
                            'format'=>'raw',
                            'enableSorting'=>false,
                        ],

                        [
                            'class' => 'renk\yiipal\grid\DataColumn',
                            'label' => '创建时间',
                            'contentOptions'=>['style'=>'max-width: 150px;min-width: 85px;'],
                            'filter'=>function($model){
                                $gets = Yii::$app->request->get('Order',[]);
                                $created_at = isset($gets['created_at'])?$gets['created_at']:'';
                                $output = '<input type="text" class="form-control form-daterange pull-right" name="Order[created_at]" value="'.$created_at.'"/>';
                                return $output;
                            },
                            'attribute' => 'created_at',
                            'value' => function ($data) {
                                return '<span class="item">'.date("Y-m-d H:i:s",$data->created_at).'</span>';
                            },
                            'format' => 'raw',
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '旧【尺码|刻字】',
                            'attribute' => 'product_options',
                            'contentOptions'=>['style'=>'min-width: 170px;max-width: 170px;'],
                            'value' => function ($model) {
                                $output = '';
                                $items = $model->items;
                                foreach($items as $item){
                                    if(empty($item->last_item_info)){
                                        continue;
                                    }
                                    $data = json_decode($item->last_item_info);
                                    $output .= '<span class="item">';
                                    if(isset($data->engravings) && !empty($data->engravings)){
                                        $output .= '刻字内容：'.$data->engravings.'<br/>';
                                    }
                                    if(isset($data->size_original)){
                                        $output.= '尺码:'.$data->size_original.'<span class="text-danger">[美国码:'.$data->size_us.']</span>';
                                    }
                                    if($item->size_type != 'none'){
                                        $output.='['.\app\helpers\Options::ringTypes($item->size_type).']';
                                    }
                                    $output .='</span>';
                                }
                                return $output;
                            },
                            'format' => 'raw',
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '新【尺码|刻字】',
                            'attribute' => 'product_options',
                            'contentOptions'=>['style'=>'min-width: 170px;max-width: 170px;'],
                            'value' => function ($model) {
                                $output = '';
                                $items = $model->items;
                                foreach($items as $data){
                                    if(empty($data->last_item_info)){
                                        continue;
                                    }
                                    $output .= '<span class="item">';
                                    if(!empty($data->engravings)){
                                        $output .= '刻字内容：'.$data->engravings.'<br/>';
                                    }
                                    $output.= '尺码:'.$data->size_original.'<span class="text-danger">[美国码:'.$data->size_us.']</span>';
                                    if($data->size_type != 'none'){
                                        $output.='['.\app\helpers\Options::ringTypes($data->size_type).']';
                                    }
                                    $output .='</span>';
                                }
                                return $output;
                            },
                            'format' => 'raw',
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '状态',
                            'contentOptions'=>['style'=>'max-width: 75px;'],
                            'filter'=>ItemStatus::factoryConfirmStatus(),
                            'attribute' => 'factory_change_confirmed_status',
                            'value' => function ($data) {
                                $items = $data->items;
                                $output = '';
                                foreach($items as $item){
                                    if(empty($item->last_item_info)){
                                        continue;
                                    }
                                    $output.='<span class="item">'.ItemStatus::factoryConfirmStatus($item->factory_change_confirmed_status).'</span>';
                                }
                                return $output;
                            },
                            'format' => 'raw',
                        ],

                        [
                            'class' => 'yii\grid\DataColumn',
                            'label' => '备注',
                            'contentOptions'=>['style'=>'max-width: 75px;'],
                            'attribute' => 'comment',
                            'value' => function ($data) {
                                $output = Html::a('有',['comment/list?target_id='.$data->id.'&type=order'],['class'=>'ajax-modal','title'=>'备注']);
                                if(empty($data->comments)){
                                    $output = '无';
                                }
                                return '<span class="item">'.$output.'</span>';
                            },
                            'format' => 'raw',
                        ],
                    ],
                ]); ?>
            </div>
        </div>

    </div>
</div>
