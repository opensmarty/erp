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
use app\models\product\Size;
use app\models\product\Stock;
use app\models\shipment\ShipmentLog;
use app\models\supplies\Material;
use renk\yiipal\helpers\Url;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use app\models\order\Address;

class Item extends BaseModel{

    // 等待处理
    const TASK_STATUS_PENDING				= 'pending';
    // 已取消
    const TASK_STATUS_CANCELLED				= 'cancelled';
    // 等待发货
    const TASK_STATUS_WAITING_SHIPPED		= 'waiting_shipped';
    // 已经发货
    const TASK_STATUS_SHIPPED				= 'shipped';

    // 等待配货
    const TASK_STATUS_PICK_WAITING			= 'pick_waiting';
    // 配货中
    const TASK_STATUS_PICKING				= 'picking';
    // 配货完成
    const TASK_STATUS_PICK_COMPLETED		= 'pick_completed';

    // 等待采购
    const TASK_STATUS_PENDING_PURCHASE	    = 'pending_purchase';
    // 采购中
    const TASK_STATUS_PURCHASE			    = 'purchase';
    // 采购完成
    const TASK_STATUS_PURCHASE_COMPLETED	= 'purchase_completed';

    // 备货中
    const TASK_STATUS_PROCESSING			= 'processing';
    // 交易完成
    const TASK_STATUS_COMPLETE				= 'complete';

    // 等待验收
    const TASK_STATUS_WAIT_ACCEPT			= 'waiting_accept';
    // 等待返修
    const TASK_STATUS_WAITING_REPAIR		= 'waiting_repair';
    // 开始返修
    const TASK_STATUS_BEING_REPAIRED		= 'being_repaired';
    // 等待生产
    const TASK_STATUS_WAITING_PRODUCTION	= 'waiting_production';
    // 生产中
    const TASK_STATUS_IN_PRODUCTION			= 'in_production';
    // 验货通过
    const TASK_STATUS_PRODUCT_PASSED		= 'product_passed';
    // 返修中
    const TASK_STATUS_REWORK				= 'rework';
    // 已加入库存
    const TASK_STATUS_IN_STOCK				= 'in_stock';


    // 退货中
    const TASK_STATUS_RETURN_PROCESS        = 'return_process';
    // 退货中(部分)
    const TASK_STATUS_RETURN_PROCESS_PART   = 'return_process_part';

    // 已退回
    const TASK_STATUS_RETURN_COMPLETED      = 'return_completed';
    // 已退回(部分)
    const TASK_STATUS_RETURN_COMPLETED_PART = 'return_part_completed';

    // 换货中
    const TASK_STATUS_EXCHANGE_PROCESS      = 'exchange_process';
    // 换货中(部分)
    const TASK_STATUS_EXCHANGE_PROCESS_PART = 'exchange_process_part';

    // 换货完成
    const TASK_STATUS_EXCHANGE_COMPLETED    = 'exchange_completed';
    // 换货完成(部分)
    const TASK_STATUS_EXCHANGE_COMPLETED_PART   = 'exchange_part_completed';


    //订单编号
    public $ext_order_id = '';

    public $shipping_method = '';

    public $shipping_track_no = '';

    public $last_track_status = '';
    public $expedited = '';
    public $process_at = '';
    public $paused = '';
    public $exceed = '';
    public $allStocks = [];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'order_item';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = [
            [['sku','ext_order_id','process_at','increment_id','expedited','paused','shipping_method','shipping_track_no',
                'last_track_status','item_type','created_at','item_status','has_engravings','has_rejects','exceed'],'safe'],
            [['sku','qty_ordered','size_original','size_us'],'required','on'=>['create']],
        ];

        return $rules;
    }

    /**
     * 设置Label
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'sku' => 'SKU',
            'qty_ordered' => '数量',
            'size_us' => '实际尺码',
            'size_original' => '网站尺码',
            'engravings' => '刻字内容',
        ];
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
        $query = $class::find()->with('order')->with('product')->with('comments')->with('orderExpedited')
            ->innerJoin('order','order_item.order_id = order.id');

        $websites = \Yii::$app->user->getIdentity()->websites;
        if(!empty($websites)){
            $query->andWhere(['in','order.source',explode(",",$websites)]);
        }

        // add conditions that should always apply here
        $this->load($params);
        
        if(isset($params['Item']['ext_order_id'])){
            $query->andFilterWhere(['LIKE', 'ext_order_id', $params['Item']['ext_order_id']]);
            unset($params['Item']['ext_order_id']);
        }
        
        if(isset($params['Item']['sku'])){
            $query->andFilterWhere(['LIKE', 'sku', $this->getAttribute('sku')]);
            unset($params['Item']['sku']);
        }
        
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> ['defaultOrder' => ['created_at'=>SORT_DESC]]
        ]);

        $this->formatQueryParams($query, $params,$defaultParams);

        return $dataProvider;
    }

    /**
     * 处理查询参数.
     * @param $params
     * @return array
     */
    public function formatSearchParams($params){
        $output = [];
        foreach($params as $field=>$item){
            $item = trim($item);
            if($item == ''){
                continue;
            }
            if($field === 'exceed'){
                $operator = $item>0?"<":">";
                $deliveryTime = time()-$this->getDeliveryTime();
                $output[] = [$operator,'order.process_at',$deliveryTime];
                continue;
            }

            $orderParams = ['ext_order_id','increment_id','payment_method','paused','expedited','created_at','process_at'];
            $itemParams = ['item_type','has_engravings'];
            switch($field){
                case in_array($field, $orderParams):
                    $field = 'order.'.$field;
                    break;
                case in_array($field, $itemParams):
                    $field = 'order_item.'.$field;
                    break;
            }
            $output[] = [$field=>$item];
        }
        return $output;
    }

    /**
     * 获取Item对应的订单项目.
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(Order::className(), ['id' => 'order_id']);
    }

    /**
     * 获取Item对应的库存数据.
     * @return \yii\db\ActiveQuery
     */
    public function getStocks()
    {
        return $this->hasOne(Stock::className(), ['product_id' => 'product_id'])
            ->where('size_code = :size_code AND type = :size_type', [':size_code' => $this->size_us,':size_type'=>$this->size_type])
            ;
    }


    /**
     * 获取Item的产品信息
     * @return \yii\db\ActiveQuery
     */
    public function getProduct(){
        return $this->hasOne(Product::className(), ['id' => 'product_id']);
    }

    /**
     * 获取加急信息
     * @return \yii\db\ActiveQuery
     */
    public function getOrderExpedited(){
        return $this->hasOne(OrderExpedited::className(), ['order_id' => 'order_id']);
    }

    /**
     * 获取地址信息
     * @return $this
     */
    public function getAddress(){
        return $this->hasOne(Address::className(), ['parent_id' => 'order_id'])
            ->where('address_type = :threshold', [':threshold' => 'shipping'])
            ;
    }

    /**
     * 获取订单备注
     * @return $this
     */
    public function getComments(){
        $uid = \Yii::$app->user->id;
        return $this->hasMany(Comment::className(), ['target_id' => 'order_id'])
            ->where('type=:threshold1 AND FIND_IN_SET(:threshold2, visible_uids)', [':threshold1' => 'order',':threshold2' => $uid])
            ;
    }

    /**
     * 处理订单（分发 生产/配货/采购 任务）
     * @param $ordersInfo
     */
    public function processItem($ordersInfo){
        $refactorOrders = [];
        $orderIds = array_keys($ordersInfo);
        $orderItems = Item::find()->andWhere(['in','order_id',$orderIds])->all();
        $productIds = array_keys(ArrayHelper::index($orderItems,'product_id'));
        $products = Product::find()->from(Product::tableName())->with('stocks')->andWhere(['in','id', $productIds])->all();
        $products = ArrayHelper::index($products,'id');

        foreach($orderItems as $item){
            if(!isset($products[$item['product_id']])) continue;
            //退换货的订单，存在部分Item已经是发货的状态，不必再次处理
            if($item->item_status == Item::TASK_STATUS_SHIPPED) continue;
            //处理之前，已经取消的单子，不需要再处理.
            if($item->item_status == Item::TASK_STATUS_CANCELLED) continue;
            $product = $products[$item['product_id']];
            $item->item_status = self::getItemStatusByType($item->item_type);
            //虚拟产品直接设置为发货状态
            if($product->type=='virtual'){
                $item->item_status = Item::TASK_STATUS_SHIPPED;
            }
            $item->save();

            if($product->type!='virtual'){
                $refactorOrders[$item->order_id][] = $item;
            }
        }
        return $refactorOrders;
    }

    /**
     * 根据产品类型获取状态
     * @param $type
     * @return string
     */
    public static function getItemStatusByType($type){
        $status = '';
        switch($type){
            case Order::ORDER_TYPE_STOCK:
                $status = Item::TASK_STATUS_PICK_WAITING;
                break;
            case Order::ORDER_TYPE_CUSTOM:
                $status = Item::TASK_STATUS_WAITING_PRODUCTION;
                break;
            case Order::ORDER_TYPE_TB:
                $status = Item::TASK_STATUS_PENDING_PURCHASE;
                break;
        }
        return $status;
    }


    /**
     * 判断Item类型
     * @param $item
     * @param $product
     * @return string
     */
    public static function checkItemType($item,$product){
        $stocks = $product->getProductStocks();
        if(empty($stocks)){
            $isStockItem = false;
        }else{
            $sizeInfo = Size::find()->where(['size'=>$item->size_us])->one();
            $sizeId = 0;
            if(!empty($sizeInfo)){
                $sizeId = $sizeInfo->id;
            }
            $isStockItem = isset($stocks[$item->size_type][$sizeId])&&$stocks[$item->size_type][$sizeId]['total']>=$item->qty_ordered?true:false;
        }

        $type = '';
        //有库存
        if($isStockItem){
            //有库存+没有刻字=库存单
            if(empty($item->engravings)){
                $type = Order::ORDER_TYPE_STOCK;
            }
            //有刻字
            else{
                //有库存+有刻字+淘宝款=库存单
                if($product->type=='taobao'){
                    $type = Order::ORDER_TYPE_STOCK;
                }
                //有库存+有刻字+工厂款=库存单(Jeulia专属)
                elseif($product->type=='factory'){
                    $type = Order::ORDER_TYPE_STOCK;
                }
                //虚拟商品=库存单
                else{
                    $type = Order::ORDER_TYPE_STOCK;
                }
            }
        }
        //没有库存
        else{
            //没有库存+淘宝款=淘宝单
            if($product->type=='taobao') {
                $type = Order::ORDER_TYPE_TB;
            }
            //没有库存+工厂款=定制单
            elseif($product->type=='factory'){
                $type = Order::ORDER_TYPE_CUSTOM;
            }
            //虚拟商品=库存单
            else{
                $type = Order::ORDER_TYPE_STOCK;
            }
        }
        return $type;
    }

    /**
     * 获取交货时间
     * @param string $type
     * @return int
     */
    public function getDeliveryTime($type=Order::ORDER_TYPE_CUSTOM){
        $day = 60*60*24;
        if($type == Order::ORDER_TYPE_CUSTOM){
            return $day*5;
        }else{
            return $day*7;
        }
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
            'label'=>'查看',
            'url'=>Url::toRoute(['/order/order/distribution-view?id='.$this->order->id]),
            'icon'=>'glyphicon glyphicon-wrench',
            'attr' =>['class'=>'btn-order-view'],
        ];
        $buttons[] = [
            'label'=>'备注',
            'url'=>Url::toRoute(['/comment/create?target_id='.$this->order->id.'&type=order&subject='.Comment::COMMENT_TYPE_OTHERS]),
            'icon'=>'glyphicon glyphicon-wrench',
            'attr' =>['class'=>'btn-order-expedite ajax-modal'],
        ];

        $buttons[] = [
            'label'=>'联系客户',
            'url'=>Url::toRoute(['/order/issue/create?id='.$this->order->id]),
            'icon'=>'glyphicon glyphicon-wrench',
            'attr' =>['class'=>'btn-order-issue ajax-modal'],
        ];

        if(in_array($this->item_type,['stock','taobao']) && in_array($this->item_status, [Item::TASK_STATUS_WAITING_SHIPPED,Item::TASK_STATUS_PICKING])
        ){
            $buttons[] = [
                'label'=>'标记次品',
                'url'=>Url::toRoute(['/distribution/rejects/mark-rejects','id'=>$this->id]),
                'icon'=>'glyphicon glyphicon-wrench',
                'attr' =>['class'=>'btn-request-accept ajax-modal'],
            ];
        }

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
                    'url'=>Url::toRoute(['/distribution/custom/accept-request','id'=>$this->id]),
                    'icon'=>'glyphicon glyphicon-wrench',
                    'attr' =>['class'=>'btn-accept-request ajax-modal'],
                ];
                break;
            case Item::TASK_STATUS_PICK_WAITING:
                $buttons[] = [
                    'label'=>'开始配货',
                    'url'=>Url::toRoute(['/distribution/stock/start-picking','id'=>$this->id]),
                    'icon'=>'glyphicon glyphicon-wrench',
                    'attr' =>['class'=>'btn-start-picking ajax'],
                ];
                break;
            case Item::TASK_STATUS_PICKING:
                $buttons[] = [
                    'label'=>'配货完成',
                    'url'=>Url::toRoute(['/distribution/stock/complete-picking','id'=>$this->id]),
                    'icon'=>'glyphicon glyphicon-wrench',
                    'attr' =>['class'=>'btn-complete-picking ajax'],
                ];
                break;
            case Item::TASK_STATUS_PENDING_PURCHASE:
                $buttons[] = [
                    'label'=>'开始采购',
                    'url'=>Url::toRoute(['/distribution/taobao/start-purchase','id'=>$this->id]),
                    'icon'=>'glyphicon glyphicon-wrench',
                    'attr' =>['class'=>'btn-start-purchase ajax'],
                ];
                break;
            case Item::TASK_STATUS_PURCHASE:
                $buttons[] = [
                    'label'=>'采购完成',
                    'url'=>Url::toRoute(['/distribution/taobao/complete-purchase','id'=>$this->id]),
                    'icon'=>'glyphicon glyphicon-wrench',
                    'attr' =>['class'=>'btn-complete-purchase ajax'],
                ];
                break;
            case Item::TASK_STATUS_WAITING_SHIPPED:
                $url = Url::toRoute(['/distribution/custom/ship','id'=>$this->id]);
                if($this->order->order_type == Order::ORDER_TYPE_MIXTURE){
                    $url = Url::toRoute(['/distribution/custom/ship','id'=>$this->order->id,'type'=>'order']);
                }
                $buttons[] = [
                    'label'=>'发货',
                    'url'=>$url,
                    'icon'=>'glyphicon glyphicon-wrench',
                    'attr' =>['class'=>'btn-complete-purchase ajax download'],
                ];
                break;
        }
        return $buttons;
    }

    /**
     * 开始生产
     * @param $ids
     */
    public function startProduce($ids){
        $items = self::find()->with('order')->andWhere(['in','id',$ids])->all();
        $validIds = [];
        $orderIds = [0];
        foreach($items as $item){
            if($item->item_status == self::TASK_STATUS_WAITING_PRODUCTION){
                $validIds[] = $item->id;
                $orderIds[] = $item->order_id;
            }
            OrderStatusTracking::track($item->order,'开始生产:'.$item->sku);
        }
        Item::updateAll(['item_status' => self::TASK_STATUS_IN_PRODUCTION],['in','id',$validIds]);
        $order = new Order();
        $order->updateOrderStatus($orderIds,self::TASK_STATUS_IN_PRODUCTION);
    }

    /**
     * 加急确认
     * @param $ids
     */
    public function expeditedConfirm($ids){
        $items = self::find()->with('order')->andWhere(['in','id',$ids])->all();
        $validIds = [];
        foreach($items as $item){
                $validIds[] = $item->order_id;
                OrderStatusTracking::track($item->order,'确认加急');
        }
        OrderExpedited::updateAll(['status' => OrderExpedited::TASK_STATUS_CONFIRMED,'uid'=>$this->getCurrentUid(),'confirmed_at'=>time()],['in','order_id',$validIds]);
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
        $item->item_status = self::TASK_STATUS_WAIT_ACCEPT;
        $item->save();
        OrderStatusTracking::track($item->order,'请求验收:'.$item->sku.' '.$number.' 个');
        $order = new Order();
        $order->updateOrderStatus([$item->order_id],self::TASK_STATUS_WAIT_ACCEPT);
    }

    /**
     * 验收通过
     * @param $id
     * @param $number
     */
    public function acceptRequest($id, $post){
        $number = $post['number'];
        $item = self::findOne(['id'=>$id]);
        $qty_delivery = $item->qty_delivery;
        $item->qty_passed += $item->qty_delivery;
        $item->qty_delivery = 0;
        if($item->qty_ordered<=$item->qty_passed){
            $item->item_status = self::TASK_STATUS_WAITING_SHIPPED;
        }else{
            $item->item_status = self::TASK_STATUS_IN_PRODUCTION;
        }
        if($number>0){
            $item->has_rejects = 1;
        }
        $item->save();
        $pk = $item->getPrimaryKey();
        //记录次品
        if($number>0){
            $reject_tags = $post['reject_tags'];
            $rejected = StockOrderRejected::find()->where(['item_id'=>$pk])->one();
            if(empty($rejected)){
                $rejected = new StockOrderRejected();
            }
            $rejected->order_id = $item->order_id;
            $rejected->item_id = $pk;
            $rejected->product_id = $item->product_id;
            $rejected->item_type = 'custom';
            $rejected->sku = $item->sku;
            $rejected->product_type = $item->size_type;
            $rejected->size_us = $item->size_us;
            $rejected->has_engravings = $item->has_engravings;
            $rejected->engravings = $item->engravings;
            $rejected->item_status = 'rejected';
            $rejected->reject_tags = $reject_tags;
            $rejected->report_uid = $this->getCurrentUid();
            $rejected->qty_rejected = $rejected->qty_rejected+$number;
            $rejected->save();
        }

        //记录验货通过数
        if($qty_delivery>0){
            $productDelivered = new ProductDelivered();
            $productDelivered->order_id = $item->order_id;
            $productDelivered->item_id = $item->id;
            $product = Product::findOne($item->product_id);
            $productDelivered->product_id = $product->id;
            $productDelivered->sku = $item->sku;
            $productDelivered->price = $product->price;
            $productDelivered->qty_ordered = $item->qty_ordered;
            $productDelivered->qty_passed_total = $item->qty_passed;
            $productDelivered->qty_passed = $qty_delivery;
            $productDelivered->uid = $this->getCurrentUid();
            $productDelivered->order_type = ProductDelivered::DELIVERED_TYPE_CUSTOM;
            $productDelivered->product_type = $item->size_type;
            $productDelivered->size_us = $item->size_us;
            $productDelivered->engravings = $item->engravings;
            $productDelivered->has_engravings = empty($item->engravings)?0:1;
            $productDelivered->start_at = $item->order->process_at;
            $productDelivered->expedited = $item->order->expedited;
            $productDelivered->save();
            $productDelivered->duration_time = $productDelivered->created_at-$productDelivered->start_at;
            $productDelivered->save();
            $productDelivered::updateAll(['qty_passed_total' => $productDelivered->qty_passed_total],['order_id'=>$productDelivered->order_id]);
        }

        //更新订单状态
        $this->checkItemForOrderShip($item);
        OrderStatusTracking::track($item->order,'验收通过:'.$item->sku.' '.$qty_delivery.' 个');
    }

    /**
     * 开始配货
     * @param $ids
     */
    public function startPicking($ids){
        $items = self::find()->where(['in','id',$ids])->all();
        $validIds = [];
        $orderIds = [0];
        foreach($items as $item){
            if($item->item_status == self::TASK_STATUS_PICK_WAITING){
                $validIds[] = $item->id;
                $orderIds[] = $item->order_id;
                OrderStatusTracking::track($item->order,'开始配货:'.$item->sku);
            }
        }
        Item::updateAll(['item_status' => self::TASK_STATUS_PICKING],['in','id',$validIds]);
        $order = new Order();
        $order->updateOrderStatus($orderIds,self::TASK_STATUS_PICKING);
        return $validIds;
    }

    /**
     * 发货
     * @param $ids
     */
    public function ship($ids,$type='item'){
        if($type=='item'){
            return $this->shipByItemIds($ids);
        }else{
            return $this->shipByOrderIds($ids);
        }
    }

    /**
     * 发货记录
     * @param $order
     */
    private function shipLog($orders){
        foreach($orders as $order){
            $shipmentLog = new ShipmentLog();
            $shipmentLog->order_id = $order->id;
            $shipmentLog->ext_order_id = $order->ext_order_id;
            $shipmentLog->increment_id = $order->increment_id;
            $shipmentLog->ship_uid = $this->getCurrentUid();
            $shipmentLog->save();
            OrderStatusTracking::track($order,'发货');
        }
    }

    /**
     * 根据Item ids发货
     * @param $ids
     * @return array
     */
    private function shipByItemIds($ids){
        $items = self::find()->with('order')->where(['in','id',$ids])->all();
        $validIds = [];
        $validOrderIds = [];
        $validOrders = [];
        foreach($items as $item){
            if($this->checkOrderCanShip($item->order,[$item])){
                $validIds[] = $item->id;
                $validOrderIds[] = $item->order->id;
                $validOrders[] = $item->order;
            }
        }

        $material = new Material();
        $material->reduceMaterialByOrderIds($validOrderIds);

        Item::updateAll(['item_status' => self::TASK_STATUS_SHIPPED],['in','id',$validIds]);
        Order::updateAll(['status' => self::TASK_STATUS_SHIPPED,'shipped_at'=>time()],['in','id',$validOrderIds]);
        $this->shipLog($validOrders);
        return array_diff($ids,$validIds);
    }

    /**
     * 根据Order ids发货
     * @param $ids
     * @return array
     */
    private function shipByOrderIds($ids){
        $orders = Order::find()->with('items')->where(['in','id',$ids])->all();
        $validIds = [];
        $validOrders = [];
        foreach($orders as $order){
            if($this->checkOrderCanShip($order,$order->items)){
                $validIds[] = $order->id;
                $validOrders[] = $order;
            }
        }

        $material = new Material();
        $material->reduceMaterialByOrderIds($validIds);

        Item::updateAll(['item_status' => self::TASK_STATUS_SHIPPED],['in','order_id',$validIds]);
        Order::updateAll(['status' => self::TASK_STATUS_SHIPPED,'shipped_at'=>time()],['in','id',$validIds]);
        $this->shipLog($validOrders);
        return array_diff($ids,$validIds);
    }


    /**
     * 检查order是否可以发货
     * @param $order
     * @return bool
     */
    private function checkOrderCanShip($order,$items = false){
        $flag = true;
        foreach($items as $item){
            if($item->has_rejects == 1){
                $flag = false;
            }
        }
        if(
            $flag == true
            && $order->status == self::TASK_STATUS_WAITING_SHIPPED
            && $order->paused == 0
            && $order->shipping_method != ''
            && $order->shipping_track_no != ''
            && in_array($order->last_track_status,['normal','change_confirmed'])
        ){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 配货完成
     * @param $ids
     */
    public function completePicking($ids){
        $items = self::find()->where(['in','id',$ids])->all();
        $validIds = [];
        foreach($items as $item){
            if($item->item_status == self::TASK_STATUS_PICKING){
                $validIds[] = $item->id;
            }
        }
        Item::updateAll(['item_status' => self::TASK_STATUS_WAITING_SHIPPED],['in','id',$validIds]);

        //配货完成后检查是否order可以等待发货
        $items = self::find()->with('order')->where(['in','id',$validIds])->all();
        foreach($items as $item){
            $this->checkItemForOrderShip($item);
            OrderStatusTracking::track($item->order,'配货完成:'.$item->sku);
        }
    }


    /**
     * 开始采购
     * @param $ids
     */
    public function startPurchase($ids){
        $items = self::find()->with('order')->where(['in','id',$ids])->all();
        $validIds = [];
        $orderIds = [0];
        foreach($items as $item){
            if($item->item_status == self::TASK_STATUS_PENDING_PURCHASE){
                $validIds[] = $item->id;
                $orderIds = $item->order_id;
                OrderStatusTracking::track($item->order,'开始采购:'.$item->sku);
            }
        }
        Item::updateAll(['item_status' => self::TASK_STATUS_PURCHASE],['in','id',$validIds]);
        $order = new Order();
        $order->updateOrderStatus($orderIds,self::TASK_STATUS_PICKING);
    }

    /**
     * 采购完成
     * @param $ids
     */
    public function completePurchase($ids){
        $items = self::find()->where(['in','id',$ids])->all();
        $validIds = [];
        foreach($items as $item){
            if($item->item_status == self::TASK_STATUS_PURCHASE){
                $validIds[] = $item->id;
            }
        }
        Item::updateAll(['item_status' => self::TASK_STATUS_WAITING_SHIPPED],['in','id',$validIds]);

        //配货完成后检查是否order可以等待发货
        $items = self::find()->with('order')->where(['in','id',$validIds])->all();
        foreach($items as $item){
            $this->checkItemForOrderShip($item);
            OrderStatusTracking::track($item->order,'采购完成:'.$item->sku);
        }
    }



    /**
     * 检查是否所有Item都已经可以发货，更新Order状态为等待发货.
     * @param $item
     */
    private function checkItemForOrderShip($item){
        $order = $item->order;
        $orderItems = $order->items;
        $readyForShip = 1;
        foreach($orderItems as $orderItem){
            if($orderItem->item_status == self::TASK_STATUS_CANCELLED || $orderItem->item_status == self::TASK_STATUS_SHIPPED){
                continue;
            }
            if($orderItem->item_status != self::TASK_STATUS_WAITING_SHIPPED){
                $readyForShip = 0;
            }
        }
        if($readyForShip){
            $order->status = self::TASK_STATUS_WAITING_SHIPPED;
            $order->save();
        }
    }


    /**
     * 修改SKU
     */
    public function changeSku($newSku){
        $oldItem = self::findOne($this->id);
        $this->processChangeSku($oldItem,$newSku);

        $order = Order::findOne($this->order_id);
        if($order->status !=Item::TASK_STATUS_PENDING){
            $order->last_track_status = Order::TASK_STATUS_ITEM_CHANGED;
        }
        $order->save();
        Order::archiveOrder($order->id,'修改SKU');
    }

    /**
     * 修改SKU处理流程
     * @param $oldItem
     * @param $newSku
     */
    private function processChangeSku($oldItem,$newSku){
        $oldOrder = Order::findOne($oldItem->order_id);

        $oldProduct = Product::findOne($oldItem->product_id);
        //审核通过之前，不做库存扣减
        if($oldOrder->approved ==1){
            //如果是库存款，把减掉的库存加回去
            if($oldItem->item_type=='stock'){
                Stock::increaseStocks($oldProduct,$oldItem->qty_ordered,$oldItem->size_us,$oldItem->size_type);
            }
            //如果是定制款,已经验收通过了，就增加对应的库存
            if($oldItem->item_type=='custom' && $oldItem->qty_passed>0){
                Stock::increaseStocks($oldProduct,$oldItem->qty_passed,$oldItem->size_us,$oldItem->size_type);
            }

            //如果是淘宝款，并且已经开始采购了，就直接入库存
            if($oldItem->item_type=='taobao' && $oldItem->item_status!= Item::TASK_STATUS_PENDING_PURCHASE){
                Stock::increaseStocks($oldProduct,$oldItem->qty_ordered,$oldItem->size_us,$oldItem->size_type);
            }
        }

        $product = Product::find()->where(['sku'=>$newSku])->one();
        //重新判定Item类型和状态
        $itemType = self::checkItemType($this, $product);
        $this->item_type= $itemType;
        if($oldOrder->approved ==1){
            $this->item_status = self::getItemStatusByType($this->item_type);
        }
        $this->sku = $newSku;
        $this->product_id=$product->id;
        //重置必要的字段
        $this->qty_delivery = 0;
        $this->qty_passed = 0;
        $this->save();

        //如果新修改的产品是库存款，减少对应的库存
        if($this->item_type == 'stock' && $oldOrder->approved ==1){
            Stock::reduceStocks($product, $this->qty_ordered,$this->size_us,$this->size_type);
        }

        //更新订单状态,如果订单是混合单，修改为备货中，否则，订单类型就和Item类型一致。如果订单状态为pending,则不需要修改
        if($oldOrder->status != Item::TASK_STATUS_PENDING){
            if($oldOrder->order_type == Order::ORDER_TYPE_MIXTURE){
                $oldOrder->status = Item::TASK_STATUS_PROCESSING;
            }else{
                $oldOrder->status = $this->item_status;
                $oldOrder->order_type = $this->item_type;
            }
            $oldOrder->save();
        }
    }

    /**
     * 修改数量
     */
    public function changeQuantity($qty){
        $this->processChangeQuantity($qty);
        $order = Order::findOne($this->order_id);
        if($order->status !=Item::TASK_STATUS_PENDING){
            $order->last_track_status = Order::TASK_STATUS_ITEM_CHANGED;
        }
        $order->save();
        Order::archiveOrder($order->id,'修改订单数量');
    }

    /**
     * 处理修改数量流程.
     * @param $qty
     */
    private function processChangeQuantity($qty){
        $product = Product::findOne($this->product_id);
        $diffQty = $this->qty_ordered-$qty;
        //审核通过之前，不做库存扣减
        if($this->order->approved ==1){
            //如果是库存款
            if($this->item_type=='stock' ){
                //减少订单量，则增加库存
                Stock::increaseStocks($product,$diffQty,$this->size_us,$this->size_type);
            }
        }

        $this->qty_ordered=$qty;
        $this->save();
    }

    /**
     * 修改尺码
     */
    public function changeSize($sizeUs, $sizeOriginal){
        $this->processChangeSize($sizeUs, $sizeOriginal);
        $order = Order::findOne($this->order_id);
        if($order->status !=Item::TASK_STATUS_PENDING){
            $order->last_track_status = Order::TASK_STATUS_ITEM_CHANGED;
        }

        $order->save();
        Order::archiveOrder($order->id,'修改产品尺码');
    }

    /**
     * 修改尺码
     * 1. 定制款 改尺码 定制款 （不处理，林霞线下处理）
     * 2. 定制款 改尺码 库存款（工厂自己留下作为库存，我们用库存直接发货）
     * 3. 库存款 改尺码 定制款（重新生产）
     * 处理修改尺码流程.
     * @param $qty
     */
    private function processChangeSize($sizeUs, $sizeOriginal){
        $product = Product::findOne($this->product_id);
        //审核通过之前，不做库存扣减
        if($this->order->approved ==1) {
            //如果是库存款
            if ($this->item_type == 'stock') {
                //减少订单量，则增加库存
                Stock::increaseStocks($product, $this->qty_ordered, $this->size_us, $this->size_type);
            }
        }

        //保留修改尺码和刻字之前的数据
        $old_size_us = $this->size_us;
        $old_size_original = $this->size_original;

        $this->size_us = $sizeUs;
        $this->size_original = $sizeOriginal;
        $oldItemType = $this->item_type;
        //重新判定Item类型和状态
        $itemType = self::checkItemType($this, $product);
        //改尺码前后，如果都是定制款或者淘宝款，指保存新的尺码，不做其他处理
        if(($itemType == 'custom' && $oldItemType == 'custom') || ($itemType == 'taobao' && $oldItemType == 'taobao')){
            //定制款改尺码后还是定制款，需要工厂进行变更确认.
            if($itemType == 'custom' && $oldItemType == 'custom' && !in_array($this->item_status,['pending','waiting_production'])){
                $last_item_info = $this->last_item_info;
                if(empty($last_item_info)){
                    $last_item_info = new \stdClass();
                }else{
                    $last_item_info = json_decode($last_item_info);
                }
                $last_item_info->size_us = $old_size_us;
                $last_item_info->size_original = $old_size_original;
                $this->last_item_info = json_encode($last_item_info);
                $this->factory_change_confirmed_status = 'pending';
            }
            $this->save();
            return true;
        }
        $this->item_type= $itemType;
        //审核通过之前，不做库存扣减和状态更新
        if($this->order->approved ==1) {
            $this->item_status = self::getItemStatusByType($this->item_type);
        }
        //重置必要的字段
        $this->qty_delivery = 0;
        $this->qty_passed = 0;
        $this->save();
        //审核通过之前，不做库存扣减
        if($this->order->approved ==1) {
            //如果新修改的产品是库存款，减少对应的库存
            if($this->item_type == 'stock'){
                Stock::reduceStocks($product, $this->qty_ordered,$this->size_us,$this->size_type);
            }
        }

        $oldOrder = Order::findOne($this->order_id);
        //更新订单状态,如果订单是混合单，修改为备货中，否则，订单类型就和Item类型一致。如果订单状态为pending,则不需要修改
        if($oldOrder->status != Item::TASK_STATUS_PENDING){
            if($oldOrder->order_type == Order::ORDER_TYPE_MIXTURE){
                $oldOrder->status = Item::TASK_STATUS_PROCESSING;
            }else{
                $oldOrder->status = $this->item_status;
                $oldOrder->order_type = $this->item_type;
            }
            $oldOrder->save();
        }
    }

    /**
     * 修改刻字
     */
    public function changeEngravings($engravings){
        $this->processChangeEngravings($engravings);
        $order = Order::findOne($this->order_id);
        if($order->status !=Item::TASK_STATUS_PENDING){
            $order->last_track_status = Order::TASK_STATUS_ITEM_CHANGED;
        }
        $order->save();
        Order::archiveOrder($order->id,'修改产品刻字');
    }

    /**
     * 处理修改刻字流程.
     * @param $qty
     */
    private function processChangeEngravings($engravings){

        //定制款刻字，保留之前的信息
        if($this->item_type == "custom" && !in_array($this->item_status,['pending','waiting_production'])){
            $last_item_info = $this->last_item_info;
            if(empty($last_item_info)){
                $last_item_info = new \stdClass();
            }else{
                $last_item_info = json_decode($last_item_info);
            }
            $last_item_info->engravings = $this->engravings;
            $this->last_item_info = json_encode($last_item_info);
            $this->factory_change_confirmed_status = 'pending';
        }

        $this->engravings=$engravings;
        if(empty($engravings)){
            $this->has_engravings=0;
        }else{
            $this->has_engravings=1;
        }

        $this->save();
    }

    /**
     * 取消某个Item
     */
    public function cancel(){

        $product = Product::findOne($this->product_id);
        //审核通过之前，不做库存扣减
        if($this->order->approved ==1) {
            //如果是库存款，把减掉的库存加回去
            if($this->item_type=='stock'){
                Stock::increaseStocks($product,$this->qty_ordered,$this->size_us,$this->size_type);
            }
            //如果是定制款,已经验收通过了，就增加对应的库存
            if($this->item_type=='custom' && $this->qty_passed>0){
                Stock::increaseStocks($product,$this->qty_passed,$this->size_us,$this->size_type);
            }

            //如果是淘宝款，并且已经开始采购了，就直接入库存
            if($this->item_type=='taobao' && $this->item_status!= Item::TASK_STATUS_PENDING_PURCHASE){
                Stock::increaseStocks($product,$this->qty_ordered,$this->size_us,$this->size_type);
            }
        }
        $this->item_status = Item::TASK_STATUS_CANCELLED;
        $this->save();

        //如果Order下的所有Items都被取消了，则直接取消order.
        /*
        $order = Order::findOne($this->order_id);
        $cancelOrder = 1;
        foreach($order->items as $item){
            if($item->item_status!=Item::TASK_STATUS_CANCELLED){
                $cancelOrder = 0;
            }
        }
        if($cancelOrder){
            $order->status = Item::TASK_STATUS_CANCELLED;
            $order->save();
            Order::archiveOrder($order->id,'取消订单所有产品');
            OrderStatusTracking::track($order,'取消订单');
        }else{
            Order::archiveOrder($order->id,'取消订单部分产品');
        }
        */
        
        /**
         * 1. item-cancel()的时候，订单中所有【非虚拟】产品如果【全部】是 已取消==》订单取消
         * 2. item->cancel()的时候，订单中所有【非虚拟】产品如果【全部】是 代发货==》订单改为代发货
         *
         * 一个item取消时
         * 当所有【非虚拟】item都取消时，order取消
         * 当所有【非虚拟】item是已取消和待发货时（至少一个），并且没有其它状态，order待发货
         * 当所有【非虚拟】item有非（待发货和已取消）时，order状态不变
         *
         */
        $order = Order::findOne($this->order_id);
        
        $item_status = [];
        foreach($order->items as $item){
            if($item->product->type != 'virtual'){
                isset($item_status[$item->item_status]) ? $item_status[$item->item_status]++ : $item_status[$item->item_status] = 1;
            }
        }
        
        $cancelled = isset($item_status[Item::TASK_STATUS_CANCELLED]) ? $item_status[Item::TASK_STATUS_CANCELLED] : 0;
        unset($item_status[Item::TASK_STATUS_CANCELLED]);
        
        $waiting_shipped = isset($item_status[Item::TASK_STATUS_WAITING_SHIPPED]) ? $item_status[Item::TASK_STATUS_WAITING_SHIPPED] : 0;
        unset($item_status[Item::TASK_STATUS_WAITING_SHIPPED]);
        
        $other_status = empty($item_status) ? 0 : 1;
        
        //当所有【非虚拟】item都取消时，order取消
        if($waiting_shipped == 0 && $other_status == 0){
            $order->status = Item::TASK_STATUS_CANCELLED;
            $order->save();
            Order::archiveOrder($order->id,'取消订单所有产品');
            OrderStatusTracking::track($order,'取消订单');
        }
        
        //当所有【非虚拟】item是已取消和待发货时（至少一个），并且没有其它状态，order待发货
        if($waiting_shipped != 0 && $other_status == 0){
            $order->status = Item::TASK_STATUS_WAITING_SHIPPED;
            $order->save();
        }
        
        //取消订单部分产品
        if($other_status != 0){
            Order::archiveOrder($order->id,'取消订单部分产品');
        }
    }

    /**
     * 检查Item配货条件,用户混合单配货.
     * @return bool
     */
    public function canDistribute(){
        if(in_array($this->item_status,[Item::TASK_STATUS_CANCELLED,Item::TASK_STATUS_SHIPPED])){
            return false;
        }else{
            return true;
        }
    }

    public function changeItemType(){

    }
}