<?php
/**
 * Order.php 
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/5/19
 */

namespace app\models\order;

use app\models\BaseModel;
use app\models\Comment;
use app\models\product\Product;
use app\models\product\ProductDelivered;
use app\models\product\Stock;
use renk\yiipal\helpers\Url;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

class StockOrder extends BaseModel{
    public $exceed = '';
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'stock_order_item';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = [
            [$this->getTableSchema()->getColumnNames(),'safe'],
            [['exceed'],'safe'],
        ];
        return $rules;
    }

    /**
     * 获取Item的产品信息
     * @return \yii\db\ActiveQuery
     */
    public function getProduct(){
        return $this->hasOne(Product::className(), ['id' => 'product_id']);
    }

    /**
     * 获取补库存生产订单备注
     * @return $this
     */
    public function getComments(){
        $uid = \Yii::$app->user->id;
        return $this->hasMany(Comment::className(), ['target_id' => 'id'])
            ->where('type=:threshold1 AND FIND_IN_SET(:threshold2, visible_uids)', [':threshold1' => 'stocks_order',':threshold2' => $uid])
            ;
    }

    /**
     * 列表页检索
     * @param null $params
     * @param null $class
     * @return ActiveDataProvider
     */
    public function search($params=null, $class=null,$defaultParams=false)
    {
        if($class == null){
            $class = self::className();
        }
        $query = $class::find()->with('comments');

        // add conditions that should always apply here
        $this->load($params);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->formatQueryParams($query, $params,$defaultParams);

        return $dataProvider;
    }

    /**
     * 格式化查询参数
     * @param $params
     * @return array
     */
    protected function formatSearchParams($params){
        $output = [];
        foreach($params as $field=>$item){
            $item = trim($item);
            if($item == ''){
                continue;
            }

            if($field === 'exceed'){
                $operator = $item>0?"<":">";
                $deliveryTime = time()-$this->getDeliveryTime();
                $output[] = [$operator,'created_at',$deliveryTime];
                continue;
            }

            if($field == 'ext_order_id'){
                $item = ltrim($item,'S-');
            }
            $output[] = [$field=>$item];
        }
        return $output;
    }

    /**
     * 列表页按钮组
     * @return array
     */
    public function buttons(){
        return $this->getItemButtons();
    }


    /**
     * 列表页按钮过滤
     * @return array
     */
    private function getItemButtons(){
        $buttons = [];
        $buttons[] = [
            'label'=>'备注',
            'url'=>Url::toRoute(['/comment/create?target_id='.$this->id.'&type=stocks_order&subject='.Comment::COMMENT_TYPE_OTHERS]),
            'icon'=>'glyphicon glyphicon-wrench',
            'attr' =>['class'=>'btn-order-expedite ajax-modal'],
        ];
        switch($this->item_status){
            case Item::TASK_STATUS_IN_PRODUCTION:
                $buttons[] = [
                    'label'=>'请求验收',
                    'url'=>Url::toRoute(['request-accept','id'=>$this->id]),
                    'icon'=>'glyphicon glyphicon-wrench',
                    'attr' =>['class'=>'btn-request-accept ajax-modal'],
                ];
                break;
            case Item::TASK_STATUS_WAIT_ACCEPT:
                $buttons[] = [
                    'label'=>'验收通过',
                    'url'=>Url::toRoute(['accept-request','id'=>$this->id]),
                    'icon'=>'glyphicon glyphicon-wrench',
                    'attr' =>['class'=>'btn-accept-request ajax-modal'],
                ];
                break;
            case Item::TASK_STATUS_PENDING_PURCHASE:
                $buttons[] = [
                    'label'=>'开始采购',
                    'url'=>Url::toRoute(['start-purchase','id'=>$this->id]),
                    'icon'=>'glyphicon glyphicon-wrench',
                    'attr' =>['class'=>'btn-start-purchase ajax'],
                ];
                break;
            case Item::TASK_STATUS_PURCHASE:
                $buttons[] = [
                    'label'=>'采购完成',
                    'url'=>Url::toRoute(['complete-purchase','id'=>$this->id]),
                    'icon'=>'glyphicon glyphicon-wrench',
                    'attr' =>['class'=>'btn-complete-purchase ajax'],
                ];
                break;
        }
        return $buttons;
    }

    /**
     * 获取交货时间
     * @param string $type
     * @return int
     */
    public function getDeliveryTime($type=Order::ORDER_TYPE_STOCKUP){
        $day = 60*60*24;
        if($type == Order::ORDER_TYPE_CUSTOM){
            return $day*5;
        }else{
            return $day*7;
        }
    }

    /**
     * 补库存订单
     * @param $product
     * @param $type
     * @param $size
     * @param $number
     */
    public function addStocks($product,$type,$size,$number){
        $stockOrder = new StockOrder();
        $stockOrder->product_id = $product->id;
        $stockOrder->sku = $product->sku;

        if($product->type == 'taobao'){
            $stockOrder->item_status = Item::TASK_STATUS_PURCHASE;
            $stockOrder->item_type = 'taobao';
        }else{
            $stockOrder->item_status = Item::TASK_STATUS_WAITING_PRODUCTION;
            $stockOrder->item_type = 'custom';
        }

        $stockOrder->price = $product->price;
        $stockOrder->product_type = $type;
        $stockOrder->size_us = $size;
        $stockOrder->qty_ordered = $number;
        $stockOrder->save();
    }


    /**
     * 开始生产
     * @param $ids
     */
    public function startProduce($ids){
        $items = self::find()->andWhere(['in','id',$ids])->all();
        $validIds = [];
        foreach($items as $item){
            if($item->item_status == Item::TASK_STATUS_WAITING_PRODUCTION){
                $validIds[] = $item->id;
            }
        }
        self::updateAll(['item_status' => Item::TASK_STATUS_IN_PRODUCTION],['in','id',$validIds]);
    }


    /**
     * 请求验收
     * @param $id
     * @param $number
     */
    public function requestAccept($id, $number){
        $item = self::findOne(['id'=>$id]);
        if($number>$item->qty_ordered){
            $number = $item->qty_ordered;
        }
        $item->qty_delivery = $number;
        $item->item_status = Item::TASK_STATUS_WAIT_ACCEPT;
        $item->save();
    }

    /**
     * 验收通过
     * @param $id
     * @param $number
     */
    public function acceptRequest($id, $post){
        $number = $post['number'];
        $item = self::findOne(['id'=>$id]);
        $qtyDelivery = $item->qty_delivery;
        $item->qty_passed += $item->qty_delivery;

        if($item->qty_passed>$item->qty_ordered){
            $item->qty_passed = $item->qty_ordered;
        }

        if($item->qty_ordered==$item->qty_passed){
            $item->item_status = Item::TASK_STATUS_PRODUCT_PASSED;
        }else{
            $item->item_status = Item::TASK_STATUS_IN_PRODUCTION;
        }
        $item->qty_delivery = 0;
        $item->save();

        //记录次品
        $pk = $item->getPrimaryKey();
        if($number>0){
            $reject_tags = $post['reject_tags'];
            $rejected = StockOrderRejected::find()->where(['item_id'=>$item->id])->one();
            if(empty($rejected)){
                $rejected = new StockOrderRejected();
            }
            $rejected->order_id = $item->id;
            $rejected->item_id = $item->id;
            $rejected->product_id = $item->product_id;
            $rejected->item_type = 'stockup';
            $rejected->sku = $item->sku;
            $rejected->product_type = $item->product_type;
            $rejected->size_us = $item->size_us;
            $rejected->item_status = 'rejected';
            $rejected->reject_tags = $reject_tags;
            $rejected->qty_rejected = $rejected->qty_rejected+$number;
            $rejected->report_uid = self::getCurrentUid();
            $rejected->save();
        }

        //记录验货通过数
        if($qtyDelivery>0){
            $productDelivered = new ProductDelivered();
            $productDelivered->order_id = $pk;
            $productDelivered->item_id = $pk;
            $product = Product::findOne($item->product_id);
            $productDelivered->product_id = $product->id;
            $productDelivered->sku = $item->sku;
            $productDelivered->price = $product->price;
            $productDelivered->qty_ordered = $item->qty_ordered;
            $productDelivered->qty_passed_total = $item->qty_passed;
            $productDelivered->qty_passed = $qtyDelivery;
            $productDelivered->uid = $this->getCurrentUid();
            $productDelivered->order_type = ProductDelivered::DELIVERED_TYPE_STOCK;
            $productDelivered->product_type = $item->product_type;
            $productDelivered->size_us = $item->size_us;
            $productDelivered->engravings = $item->engravings;
            $productDelivered->has_engravings = empty($item->engravings)?0:1;
            $productDelivered->start_at = $item->created_at;
            $productDelivered->save();
            $productDelivered->duration_time = $productDelivered->created_at-$productDelivered->start_at;
            $productDelivered->save();
            $productDelivered::updateAll(['qty_passed_total' => $productDelivered->qty_passed_total],['order_id'=>$productDelivered->order_id]);
        }

        //增加对应产品的库存数量
        $product = Product::findOne($item->product_id);
        Stock::increaseStocks($product,$qtyDelivery,$item->size_us,$item->product_type);
    }

    /**
     * 采购完成
     * @param $ids
     */
    public function completePurchase($ids){
        $items = self::find()->where(['in','id',$ids])->all();
        $validIds = [];
        foreach($items as $item){
            if($item->item_status == Item::TASK_STATUS_PURCHASE){
                $validIds[] = $item->id;

                //增加对应产品的库存数量
                $product = Product::findOne($item->product_id);
                Stock::increaseStocks($product,$item->qty_ordered,$item->size_us,$item->product_type);
            }
        }
        self::updateAll(['item_status' => Item::TASK_STATUS_PURCHASE_COMPLETED],['in','id',$validIds]);
    }
}