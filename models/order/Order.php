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
use app\models\product\Stock;
use app\models\shipment\Shipment;
use app\models\shipment\ShipmentLog;
use app\models\shipment\ShipmentReturn;
use app\models\User;
use renk\yiipal\helpers\Url;
use yii\data\ActiveDataProvider;
use app\models\order\Address;
use yii\db\Expression;

class Order extends BaseModel{

    const ORDER_TYPE_STOCK	        = 'stock';
    const ORDER_TYPE_TB		        = 'taobao';
    const ORDER_TYPE_PURCHASE		= 'purchase';
    const ORDER_TYPE_CUSTOM         = 'custom';
    const ORDER_TYPE_MIXTURE        = 'mixture';
    const ORDER_TYPE_STOCKUP        = 'stockup';


    // 正常订单
    const TASK_STATUS_NORMAL				        = 'normal';
    // 修改地址
    const TASK_STATUS_ADDRESS_CHANGED               = 'address_changed';
    // 修改产品
    const TASK_STATUS_ITEM_CHANGED                  = 'item_changed';
    // 修改物流公司
    const TASK_STATUS_SHIPPING_METHOD_CHANGED       = 'shipping_method_changed';
    // 确认修改
    const TASK_STATUS_CHANGE_CONFIRMED              = 'change_confirmed';
    // 订单暂停
    const TASK_STATUS_PAUSED                        = 'paused';

    public $sku = '';
    public $item_type = '';
    public $product_type = '';
    public $item_status = '';
    public $has_engravings = '';
    public $has_rejects = '';
    public $customer_name = '';
    public $customer_email = '';
    public $has_comment = '';
    
    public $leftJoinTables = [];

    public $products = [];
    public $allStocks = [];
    public $categories = [];
    public $solved_uid = '';
    public $report_uid = '';
    public $issue_tags = '';
    public $issue_status = '';
    public $factory_change_confirmed_status = '';

    public static function primaryKey()
    {
        return ['id'];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = [
            [['sku','grand_total','ext_order_id','increment_id','item_type','shipping_method','shipping_track_no','created_at','shipped_at',
                'payment_status','item_status','status','last_track_status','order_type','has_shipment','expedited','paused','approved',
                'from','has_engravings','has_rejects','source','customer_name','customer_email','coupon_code','has_comment','solved_uid',
                'report_uid','issue_tags','issue_status','factory_change_confirmed_status','product_type'],'safe'],
            [['grand_total'], 'number'],
            [['increment_id','shipping_method'],'required','on'=>['create']],
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
            'grand_total' => '总价',
            'ext_order_id' => '编号',
            'increment_id' => '订单号',
            'shipping_method' => '物流方式',
        ];
    }


    /**
     * 获取订单中的Item项目
     * @return $this
     */
    public function getItems()
    {
        return $this->hasMany(Item::className(), ['order_id' => 'id'])->inverseOf('order');
    }

    /**
     * 获取订单操作的历史
     * @return $this
     */
    public function getHistories()
    {
        return $this->hasMany(OrderHistory::className(), ['order_id' => 'id'])->orderBy('created_at DESC');
    }

    /**
     * 获取订单的地址信息
     * @return $this
     */
    public function getAddress(){
        return $this->hasOne(Address::className(), ['parent_id' => 'id'])
            ->where('address_type = :threshold', [':threshold' => 'shipping'])
            ;
    }


    /**
     * 获取订单备注
     * @return $this
     */
    public function getComments(){
        $uid = \Yii::$app->user->id;
        return $this->hasMany(Comment::className(), ['target_id' => 'id'])
            ->where('type=:threshold1 AND FIND_IN_SET(:threshold2, visible_uids)', [':threshold1' => 'order',':threshold2' => $uid])
            ;
    }

    /**
     * 获取订单加急信息
     * @return \yii\db\ActiveQuery
     */
    public function getOrderExpedited(){
        return $this->hasOne(OrderExpedited::className(), ['order_id' => 'id']);
    }

    /**
     * 订单问题
     * @return \yii\db\ActiveQuery
     */
    public function getOrderIssue(){
        return $this->hasOne(OrderIssue::className(),['order_id'=>'id']);
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
            if($item==''){
                continue;
            }
            $orderParams = ['ext_order_id','increment_id','payment_method','payment_status','order_type','status',
                'has_shipment','shipping_method','shipping_track_no','expedited','paused',
                'created_at','shipped_at','from','source','coupon_code'];
            $itemParams = ['item_type','sku','has_engravings','has_rejects','factory_change_confirmed_status','item_status'];
            $addressParams = ['customer_name','customer_email'];
            $commentParams = ['has_comment'];
            $orderIssueParams = ['solved_uid','report_uid','issue_status','issue_tags'];
            switch($field){
                case 'last_track_status':
                    $this->leftJoinTables['comment'] = 'comment';
                    if($item == 'normal' || $item == 'change_confirmed'){
                        $output[] = ['order.last_track_status'=>$item];
                    }else{
                        switch($item){
                            case 'address_changed':
                                $output[] = ['comment.subject'=>'change_address'];
                                break;
                            case 'item_changed':
                                $output[] = ['in','comment.subject',['change_product','change_product_size','change_product_engravings','change_product_number']];
                                break;
                            case 'shipping_method_changed':
                                $output[] = ['comment.subject'=>'change_shipping_method'];
                                break;
                        }
                    }
                    break;
                case in_array($field, $orderParams):
                    $field = 'order.'.$field;
                    $output[] = [$field=>$item];
                    break;
                case in_array($field, $itemParams):
                    $this->leftJoinTables['order_item'] = 'order_item';
                    $field = 'order_item.'.$field;
                    $output[] = [$field=>$item];
                    break;
                case 'product_type':
                    $this->leftJoinTables['product'] = 'product';
                    $output[] = ['product.type'=>$item];
                    break;
                case in_array($field, $orderIssueParams):
                    $this->leftJoinTables['order_issue'] = 'order_issue';
                    if(in_array($field,['solved_uid','report_uid'])){
                        $user = User::find()->where(['nick_name'=>$item])->one();
                        if($user){
                            $uid = $user->id;
                        }else{
                            $uid = -1;
                        }
                        $field = 'order_issue.'.$field;
                        $output[] = [$field=>$uid];
                    }elseif($field === 'issue_tags'){
                        $tags = explode(",",$item);
                        $express = ' (';
                        foreach($tags as $index =>$tag){
                            if(empty($tag)) continue;
                            $express .= $index>0?' OR ':'';
                            $express .= ' FIND_IN_SET('.$tag.',issue_tags) ';
                        }
                        $express .= ') ';
                        $output[] = new Expression($express);
                    }else{
                        $output[] = [$field=>$item];
                    }

                    break;
                case in_array($field, $commentParams):
                    $this->leftJoinTables['comment'] = 'comment';
                    $field = 'comment.target_id';
                    if($item == 1){
                        $output[] = new Expression(" comment.target_id IS NOT null ");
                    }else{
                        $output[] = new Expression(" comment.target_id IS null ");
                    }
                    break;
                case in_array($field, $addressParams):
                    $this->leftJoinTables['order_address'] = 'order_address';
                    $field = 'order_address.'.$field;
                    if($field == 'order_address.customer_name'){
                        $output[] = new Expression(" concat(order_address.firstname,' ',order_address.lastname) like '%$item%' ");
                    }
                    elseif($field == 'order_address.customer_email'){
                        $output[] = ['order_address.email'=>$item];
                    }
                    else{
                        $output[] = [$field=>$item];
                    }
                    break;
            }
        }
        return $output;
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
        $query = $class::find()->with('items')->with('comments')->with('orderExpedited')->with("address")
            ->with("orderIssue")
//             ->leftJoin('order_item','order_item.order_id = order.id')
//             ->leftJoin('product','order_item.product_id = product.id')
//             ->leftJoin('comment',"comment.target_id = order.id AND comment.type='order'")
//             ->leftJoin('order_address',"order_address.parent_id = order.id AND order_address.address_type='shipping'")
//             ->leftjoin('order_issue',"order_issue.order_id = order.id")
            ->distinct();

        $websites = \Yii::$app->user->getIdentity()->websites;
        if(!empty($websites)){
            $query->andWhere(['in','order.source',explode(",",$websites)]);
        }

        // add conditions that should always apply here
        $this->load($params);

        //$order_column_like = ['ext_order_id','increment_id'];
        //Jeulia取消模糊搜索
        $order_column_like = [];
        foreach($order_column_like as $order_column){
            if(isset($params['Order'][$order_column])){
                $query->andFilterWhere(['LIKE', 'order.'.$order_column, $this->getAttribute($order_column)]);
                unset($params['Order'][$order_column]);
            }
        }
        
        if(isset($params['Order']['sku'])){
            $this->leftJoinTables['order_item'] = 'order_item';
            $query->andFilterWhere(['LIKE', 'order_item.sku', $params['Order']['sku']]);
            unset($params['Order']['sku']);
        }
        
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> ['defaultOrder' => ['created_at'=>SORT_DESC]]
        ]);

        $this->formatQueryParams($query, $params,$defaultParams);
        
        foreach($this->leftJoinTables as $leftJoinTable){
            switch ($leftJoinTable){
                case 'order_item':
                    $query->leftJoin('order_item','order_item.order_id = order.id');
                    break;
                case 'product':
                    $query->leftJoin('product','order_item.product_id = product.id');
                    break;
                case 'comment':
                    $query->leftJoin('comment',"comment.target_id = order.id AND comment.type='order'");
                    break;
                case 'order_address':
                    $query->leftJoin('order_address',"order_address.parent_id = order.id AND order_address.address_type='shipping'");
                    break;
                case 'order_issue':
                    $query->leftjoin('order_issue',"order_issue.order_id = order.id");
                    break;
            }
        }
        
        return $dataProvider;
    }


    /**
     * 检查订单是否可以分发处理
     * @param $order
     * @return bool
     */
    private function canProcess($order){
        if( $order->status != Item::TASK_STATUS_PENDING
            || !in_array($order->payment_status,['processing','complete'])
            //产品待定，不能分发
            || $order->paused == 1
        ){
            return false;
        }else{
            return true;
        }
    }

    /**
     * 处理订单(分发)
     * @param array $ids
     */
    public function processOrder($ids=[]){
        $orders = self::find()->andWhere(['in','id',$ids])->all();
        $ordersInfo = [];
        foreach($orders as $order){
            if(!$this->canProcess($order)){
                continue;
            }
            $ordersInfo[$order->id] = $order;
        }

        //处理Item状态(分发)
        $refactorOrders = Item::processItem($ordersInfo);

        //处理混合单状态
        foreach($refactorOrders as $orderId => $items){
            $order = $ordersInfo[$orderId];
            //多个商品=混合单
            if(count($items)>1){
                $order->status = Item::TASK_STATUS_PROCESSING;
                $order->order_type = self::ORDER_TYPE_MIXTURE;
            }else{
                $item = reset($items);
                if($item->item_type==self::ORDER_TYPE_CUSTOM){
                    $order->status = Item::TASK_STATUS_WAITING_PRODUCTION;
                    $order->order_type = self::ORDER_TYPE_CUSTOM;
                }elseif($item->item_type==self::ORDER_TYPE_STOCK){
                    $order->status = Item::TASK_STATUS_PICK_WAITING;
                    $order->order_type = self::ORDER_TYPE_STOCK;
                }elseif($item->item_type==self::ORDER_TYPE_TB){
                    $order->status = Item::TASK_STATUS_PENDING_PURCHASE;
                    $order->order_type = self::ORDER_TYPE_TB;
                }
            }
            $order->process_at = time();
            $order->save();
            OrderStatusTracking::track($order,'开始处理');
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
            'url'=>Url::toRoute(['view','id'=>$this->id]),
            'icon'=>'glyphicon glyphicon-wrench',
            'attr' =>['class'=>'btn-order-view'],
        ];
        //打单专员的查看
        $buttons[] = [
            'label'=>'查看',
            'url'=>Url::toRoute(['/order/order/distribution-view?id='.$this->id]),
            'icon'=>'glyphicon glyphicon-wrench',
            'attr' =>['class'=>'btn-order-view'],
        ];
        $buttons[] = [
            'label'=>'备注',
            'url'=>Url::toRoute(['/comment/create?target_id='.$this->id.'&type=order&subject='.Comment::COMMENT_TYPE_OTHERS]),
            'icon'=>'glyphicon glyphicon-wrench',
            'attr' =>['class'=>'btn-order-expedite ajax-modal'],
        ];

        //pending的订单不能做任何操作
        if(!in_array($this->payment_status,['processing','complete'])){
            return $buttons;
        }

        $returnConditions = [
            Item::TASK_STATUS_SHIPPED,
            Item::TASK_STATUS_COMPLETE,
        ];
        if(in_array($this->status,$returnConditions)){
            $buttons[] = [
                'label'=>'退货退款',
                'url'=>Url::toRoute(['return','id'=>$this->id]),
                'icon'=>'glyphicon glyphicon-wrench',
                'attr' =>['class'=>'btn-order-cancel label-warning ajax-with-comment', 'data'=>['commentUrl'=>'/comment/create?target_id='.$this->id.'&type=order&subject='.Comment::COMMENT_TYPE_ORDER_RETURN_EXCHANGE]],
            ];
        }



        $buttons[] = [
            'label'=>'联系客户',
            'url'=>Url::toRoute(['/order/issue/create?id='.$this->id]),
            'icon'=>'glyphicon glyphicon-wrench',
            'attr' =>['class'=>'btn-order-issue ajax-modal'],
        ];

        if($this->payment_status == 'processing' && $this->grand_total>0){
            $buttons[] = [
                'label'=>'申请退款',
                'url'=>Url::toRoute(['/order/refund/create?id='.$this->id]),
                'icon'=>'glyphicon glyphicon-wrench',
                'attr' =>['class'=>'btn-order-issue ajax-modal'],
            ];
        }

        $buttons[] = [
            'label'=>'解决中',
            'url'=>Url::toRoute(['/order/issue/processing?id='.$this->id]),
            'icon'=>'glyphicon glyphicon-wrench',
            'attr' =>['class'=>'btn-order-processing ajax'],
        ];

        $buttons[] = [
            'label'=>'问题解决',
            'url'=>Url::toRoute(['/order/issue/solved?id='.$this->id]),
            'icon'=>'glyphicon glyphicon-wrench',
            'attr' =>['class'=>'btn-order-solved ajax'],
        ];

        $shipmentConditions = [
            Item::TASK_STATUS_SHIPPED,
            Item::TASK_STATUS_COMPLETE,
        ];
        if(in_array($this->status,$shipmentConditions) && !empty($this->shipping_track_no)){
            $buttons[] = [
                'label'=>'物流',
                'url'=>Url::toRoute(['/shipment/track/real-time-tracking','id'=>$this->id]),
                'icon'=>'glyphicon glyphicon-wrench',
                'attr' =>['class'=>'btn-order-expedite ajax-modal'],
            ];
        }


        $pauseConditions = [
            Item::TASK_STATUS_SHIPPED,
            Item::TASK_STATUS_COMPLETE,
            Item::TASK_STATUS_CANCELLED,
            Item::TASK_STATUS_RETURN_PROCESS,
            Item::TASK_STATUS_RETURN_COMPLETED,
            Item::TASK_STATUS_RETURN_PROCESS_PART,
            Item::TASK_STATUS_RETURN_COMPLETED_PART,
            Item::TASK_STATUS_EXCHANGE_PROCESS,
            Item::TASK_STATUS_EXCHANGE_COMPLETED,
            Item::TASK_STATUS_EXCHANGE_PROCESS_PART,
            Item::TASK_STATUS_EXCHANGE_COMPLETED_PART,
        ];
        if(!in_array($this->status,$pauseConditions) && $this->paused == 0){
            $buttons[] = [
                'label'=>'待定',
                'url'=>Url::toRoute(['pause','id'=>$this->id]),
                'icon'=>'glyphicon glyphicon-wrench',
                'attr' =>['class'=>'btn-order-pause ajax-with-comment', 'data'=>['commentUrl'=>'/comment/create?target_id='.$this->id.'&type=order&subject='.Comment::COMMENT_TYPE_ORDER_PAUSE]],
            ];
        }

        if( $this->paused >= 1){
            $buttons[] = [
                'label'=>'取消待定',
                'url'=>Url::toRoute(['pause-resume','id'=>$this->id]),
                'icon'=>'glyphicon glyphicon-wrench',
                'attr' =>['class'=>'btn-order-pause ajax-with-comment', 'data'=>['commentUrl'=>'/comment/create?target_id='.$this->id.'&type=order&subject='.Comment::COMMENT_TYPE_ORDER_PAUSE]],
            ];
        }

        $expediteConditions = [
            Item::TASK_STATUS_SHIPPED,
            Item::TASK_STATUS_COMPLETE,
            Item::TASK_STATUS_CANCELLED,
            Item::TASK_STATUS_RETURN_PROCESS,
            Item::TASK_STATUS_RETURN_COMPLETED,
            Item::TASK_STATUS_RETURN_PROCESS_PART,
            Item::TASK_STATUS_RETURN_COMPLETED_PART,
            Item::TASK_STATUS_EXCHANGE_PROCESS,
            Item::TASK_STATUS_EXCHANGE_COMPLETED,
            Item::TASK_STATUS_EXCHANGE_PROCESS_PART,
            Item::TASK_STATUS_EXCHANGE_COMPLETED_PART,
        ];
        if(!in_array($this->status,$expediteConditions) && $this->paused == 0){
            $buttons[] = [
                'label'=>'加急',
                'url'=>Url::toRoute(['expedite','id'=>$this->id]),
                'icon'=>'glyphicon glyphicon-wrench',
                'attr' =>['class'=>'btn-order-expedite ajax-with-comment', 'data'=>['commentUrl'=>'/comment/create?target_id='.$this->id.'&type=order&subject='.Comment::COMMENT_TYPE_ORDER_EXPEDITE]],
            ];
        }

        $cancelConditions = [
            Item::TASK_STATUS_SHIPPED,
            Item::TASK_STATUS_COMPLETE,
            Item::TASK_STATUS_CANCELLED,
            Item::TASK_STATUS_RETURN_PROCESS,
            Item::TASK_STATUS_RETURN_COMPLETED,
            Item::TASK_STATUS_RETURN_PROCESS_PART,
            Item::TASK_STATUS_RETURN_COMPLETED_PART,
            Item::TASK_STATUS_EXCHANGE_PROCESS,
            Item::TASK_STATUS_EXCHANGE_COMPLETED,
            Item::TASK_STATUS_EXCHANGE_PROCESS_PART,
            Item::TASK_STATUS_EXCHANGE_COMPLETED_PART,
        ];
        if(!in_array($this->status,$cancelConditions)){
            $buttons[] = [
                'label'=>'取消订单',
                'url'=>Url::toRoute(['cancel','id'=>$this->id]),
                'icon'=>'glyphicon glyphicon-wrench',
                'attr' =>['class'=>'btn-order-cancel label-danger ajax-with-comment', 'data'=>['commentUrl'=>'/comment/create?target_id='.$this->id.'&type=order&subject='.Comment::COMMENT_TYPE_ORDER_CANCEL]],
            ];
        }

        $returnCompleteConditions = [
            Item::TASK_STATUS_RETURN_PROCESS,
            Item::TASK_STATUS_RETURN_PROCESS_PART,
        ];
        if(in_array($this->status,$returnCompleteConditions)){
            $buttons[] = [
                'label'=>'退款完成',
                'url'=>Url::toRoute(['return-complete','id'=>$this->id]),
                'icon'=>'glyphicon glyphicon-wrench',
                'attr' =>['class'=>'btn-order-cancel label-danger ajax-with-comment', 'data'=>['commentUrl'=>'/comment/create?target_id='.$this->id.'&type=order&subject='.Comment::COMMENT_TYPE_ORDER_RETURN_EXCHANGE]],
            ];
        }

        $returnConditions = [
            Item::TASK_STATUS_SHIPPED,
            Item::TASK_STATUS_COMPLETE,
        ];
        if(in_array($this->status,$returnConditions)){
            $buttons[] = [
                'label'=>'退货换货',
                'url'=>Url::toRoute(['exchange','id'=>$this->id]),
                'icon'=>'glyphicon glyphicon-wrench',
                'attr' =>['class'=>'btn-order-cancel label-danger ajax-with-comment ', 'data'=>['alert_msg'=>($this->changed>0?'请注意！该订单已经进行过至少一次退换货操作！':''),'commentUrl'=>'/comment/create?target_id='.$this->id.'&type=order&subject='.Comment::COMMENT_TYPE_ORDER_RETURN_EXCHANGE]],
            ];
        }

        $exchangeCompleteConditions = [
            Item::TASK_STATUS_EXCHANGE_PROCESS,
            Item::TASK_STATUS_EXCHANGE_PROCESS_PART,
        ];
        if(in_array($this->status,$exchangeCompleteConditions)){
            $buttons[] = [
                'label'=>'换货完成',
                'url'=>Url::toRoute(['exchange-complete','id'=>$this->id]),
                'icon'=>'glyphicon glyphicon-wrench',
                'attr' =>['class'=>'btn-order-cancel label-danger ajax-with-comment', 'data'=>['commentUrl'=>'/comment/create?target_id='.$this->id.'&type=order&subject='.Comment::COMMENT_TYPE_ORDER_RETURN_EXCHANGE]],
            ];
        }

        $shipmentWrongConditions = [
            Item::TASK_STATUS_SHIPPED,
            Item::TASK_STATUS_COMPLETE,
        ];
        if(in_array($this->status,$shipmentWrongConditions)){
            $buttons[] = [
                'label'=>'发货错误',
                'url'=>Url::toRoute(['shipment-wrong','id'=>$this->id]),
                'icon'=>'glyphicon glyphicon-wrench',
                'attr' =>['class'=>'btn-order-cancel label-danger ajax-with-comment', 'data'=>['commentUrl'=>'/comment/create?target_id='.$this->id.'&type=order&subject='.Comment::COMMENT_TYPE_ORDER_SHIPMENT_WRONG]],
            ];
        }
        return $buttons;
    }


    /**
     * 订单加急
     */
    public function expedited(){
        $this->expedited = 1;
        $this->save();
        OrderStatusTracking::track($this,'订单加急');
        $orderExpedited = OrderExpedited::findOne(['order_id'=>$this->id]);
        if(empty($orderExpedited)){
            $orderExpedited = new OrderExpedited();
        }
        $orderExpedited->order_id=$this->id;
        $orderExpedited->status = OrderExpedited::TASK_STATUS_WAIT_CONFIRM;
        $orderExpedited->save();
    }

    /**
     * 取消订单
     */
    public function cancelOrder(){
        Order::archiveOrder($this->id,'取消订单');
        $this->status = Item::TASK_STATUS_CANCELLED;
        $this->save();
        OrderStatusTracking::track($this,'取消订单');
        $this->processCancelOrder();
    }

    /**
     * 发货错误
     */
    public function shipmentWrong(){
        $shipmentLog = ShipmentLog::findOne(['order_id'=>$this->id]);
        if(empty($shipmentLog)){
            $shipmentLog = new ShipmentLog();
        }

        $comment = Comment::find()->where(['type'=>'order','target_id'=>$this->id,'subject'=>Comment::COMMENT_TYPE_ORDER_SHIPMENT_WRONG])->one();
        if($comment){
            $shipmentLog->note = $comment->content;
        }

        $shipmentLog->status = ShipmentLog::SHIPMENT_STATUS_ADDRESS_WRONG;
        $shipmentLog->report_uid = $this->getCurrentUid();
        Order::archiveOrder($this->id,'发货错误上报');
        OrderStatusTracking::track($this,'发货错误');
        $shipmentLog->save();
    }

    /**
     * 取消订单的流程处理
     */
    private function processCancelOrder(){
        $items = Item::findAll(['order_id',$this->id]);
        foreach($items as $item){
            if($item->item_type == 'stock'){
                $product = Product::findOne($item->product_id);
                Stock::increaseStocks($product,$item->qty_ordered,$item->size_us,$item->size_type);
            }
        }
    }


    /**
     * 修改订单金额
     * @param $id
     * @param $grandTotal
     */
    public function changeGrandTotal($id,$grandTotal){
        $order = self::findOne($id);
        $order->grand_total = $grandTotal;
        $order->save();
    }


    /**
     * 修改订单物流方式
     * @param $id
     * @param $shippingMethod
     */
    public function changeShippingMethod($id, $shippingMethod){
        $order = self::findOne($id);
        $order->shipping_method = $shippingMethod;
        if($order->status !=Item::TASK_STATUS_PENDING){
            $order->last_track_status = Order::TASK_STATUS_SHIPPING_METHOD_CHANGED;
        }
        $order->save();
        Order::archiveOrder($order->id,'修改物流方式');
        //同步修改物流表
//        $shipment = Shipment::findOne(['order_id'=>$order->id]);
//        $shipment->shipping_method = $order->shipping_method;
//        $shipment->save();
    }

    /**
     * 退货退款
     * @param $id
     * @param $shippingMethod
     */
    public function returnProduct($orderId,$itemIds, $shippingMethod,$shippingTrackNo){
        Order::archiveOrder($orderId,'退货退款');
        $order = self::findOne($orderId);
        if(count($order->items)==count($itemIds)){
            $order->status = Item::TASK_STATUS_RETURN_PROCESS;
        }else{
            $order->status = Item::TASK_STATUS_RETURN_PROCESS_PART;
        }
        $order->save();
        OrderStatusTracking::track($order,'退货退款');
        $products = [];
        foreach($itemIds as $itemId){
            $item = Item::findOne($itemId);
            $item->item_status = Item::TASK_STATUS_RETURN_PROCESS;
            $item->save();
            $products[$itemId]['sku'] = $item->sku;
            $products[$itemId]['price'] = $item->price;
            $products[$itemId]['item_type'] = $item->item_type;
            $products[$itemId]['size_type'] = $item->size_type;
            $products[$itemId]['size_original'] = $item->size_original;
            $products[$itemId]['size_us'] = $item->size_us;
            $products[$itemId]['engravings'] = $item->engravings;
            $products[$itemId]['engravings_type'] = $item->engravings_type;
        }

        $shipmentReturn = new ShipmentReturn();
        $shipmentReturn->order_id = $order->id;
        $shipmentReturn->shipping_method = $shippingMethod;
        $shipmentReturn->shipping_track_no = $shippingTrackNo;
        $shipmentReturn->products = serialize($products);
        $shipmentReturn->uid = $this->getCurrentUid();
        $shipmentReturn->save();
    }

    /**
     * 退货退款完成
     * @param $id
     * @param $shippingMethod
     */
    public function returnProductComplete($orderId){
        Order::archiveOrder($orderId,'退货退款完成');
        $order = self::findOne($orderId);
        if($order->status == Item::TASK_STATUS_RETURN_PROCESS){
            $order->status = Item::TASK_STATUS_RETURN_COMPLETED;
        }

        if($order->status == Item::TASK_STATUS_RETURN_PROCESS_PART){
            $order->status = Item::TASK_STATUS_RETURN_COMPLETED_PART;
        }
        $order->save();
        OrderStatusTracking::track($order,'退货退款完成');
        foreach($order->items as $item){
            if($item->item_status == Item::TASK_STATUS_RETURN_PROCESS){
                $item->item_status = Item::TASK_STATUS_RETURN_COMPLETED;
                $item->save();
            }
        }
    }

    /**
     * 退货换货
     * @param $id
     * @param $shippingMethod
     */
    public function exchangeProduct($orderId,$itemIds, $shippingMethod,$shippingTrackNo){
        Order::archiveOrder($orderId,'退货换货');
        $order = self::findOne($orderId);
        if(count($order->items)==count($itemIds)){
            $order->status = Item::TASK_STATUS_EXCHANGE_PROCESS;
        }else{
            $order->status = Item::TASK_STATUS_EXCHANGE_PROCESS_PART;
        }
        $order->save();
        OrderStatusTracking::track($order,'退货换货');
        $products = [];
        foreach($itemIds as $itemId){
            $item = Item::findOne($itemId);
            $item->item_status = Item::TASK_STATUS_EXCHANGE_PROCESS;
            $item->save();
            $products[$itemId]['sku'] = $item->sku;
            $products[$itemId]['price'] = $item->price;
            $products[$itemId]['item_type'] = $item->item_type;
            $products[$itemId]['size_type'] = $item->size_type;
            $products[$itemId]['size_original'] = $item->size_original;
            $products[$itemId]['size_us'] = $item->size_us;
            $products[$itemId]['engravings'] = $item->engravings;
            $products[$itemId]['engravings_type'] = $item->engravings_type;
        }

        $shipmentReturn = new ShipmentReturn();
        $shipmentReturn->order_id = $order->id;
        $shipmentReturn->shipping_method = $shippingMethod;
        $shipmentReturn->shipping_track_no = $shippingTrackNo;
        $shipmentReturn->products = serialize($products);
        $shipmentReturn->uid = $this->getCurrentUid();
        $shipmentReturn->save();
    }

    /**
     * 退货换货完成
     * @param $id
     * @param $shippingMethod
     */
    public function exchangeComplete($orderId){
        Order::archiveOrder($orderId,'换货完成');
        //还原订单
        $order = self::findOne($orderId);
        $order->status = Item::TASK_STATUS_PENDING;
        $order->shipping_track_no = '';
        $order->shipped_at = '';
        $order->paused = 0;
        $order->expedited = 0;
        $order->has_shipment = 0;
        $order->changed +=1;
        $order->approved = 0;
        $order->uid = $this->getCurrentUid();
        $order->save();
        OrderStatusTracking::track($order,'换货完成');
        foreach($order->items as $item){
            if($item->item_status == Item::TASK_STATUS_EXCHANGE_PROCESS){
                $item->item_status = Item::TASK_STATUS_PENDING;
                $item->qty_delivery = 0;
                $item->qty_passed = 0;
                $item->changed +=1;
                $item->init = 1;
                $item->save();
            }
        }
    }

    /**
     * 记录订单历史
     * @param $orderId
     */
    public static function archiveOrder($orderId,$operation){
        $orderInfo = Order::find()->with('items')->with('address')->where(['id'=>$orderId])->asArray()->one();
        $orderHistory = new OrderHistory();
        $orderHistory->order_id = $orderId;
        $orderHistory->ext_order_id = $orderInfo['ext_order_id'];
        $orderHistory->increment_id = $orderInfo['increment_id'];
        $orderHistory->uid = self::getCurrentUid();
        $orderHistory->order_info = json_encode($orderInfo);
        $orderHistory->operation = $operation;
        $orderHistory->save();
    }

    /**
     * 打单专员确认变更
     * @param $orderIds
     */
    public function changeConform($orderIds){
        foreach($orderIds as $orderId){
            Order::archiveOrder($orderId,'变更确认');
        }
        Order::updateAll(['last_track_status' => Order::TASK_STATUS_CHANGE_CONFIRMED],['in','id',$orderIds]);
    }

    /**
     * 工厂确认变更
     * @param $orderIds
     */
    public function factoryChangeConform($orderIds){
        Item::updateAll(['factory_change_confirmed_status' => 'solved'],['and',['in','order_id',$orderIds],['factory_change_confirmed_status'=>'pending']]);
    }

    /**
     * 审批通过新创建的订单
     * @param $orderIds
     */
    public function approveOrder($orderIds){
        foreach($orderIds as $orderId){
            Order::archiveOrder($orderId,'审批通过');
        }
        Order::updateAll(['approved' => 1],['in','id',$orderIds]);
        $orders = Order::find()->with('items')->where(['in','id',$orderIds])->all();
        foreach($orders as $order){
            foreach($order->items as $item){
                //如果是库存款，减少对应的库存
                if($item->item_type == 'stock'){
                    $product = Product::findOne($item->product_id);
                    Stock::reduceStocks($product, $item->qty_ordered,$item->size_us,$item->size_type);
                }
            }
        }
    }

    /**
     * 批量更新订单状态
     * @param $orderIds
     * @param $status
     */
    public function updateOrderStatus($orderIds, $status){
        Order::updateAll(['status' => $status],['and',['in','order_type',['stock','custom','taobao']],['in','id',$orderIds]]);
    }
}