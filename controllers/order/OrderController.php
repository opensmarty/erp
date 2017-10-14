<?php
/**
 * OrderController.php 
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/5/19
 */
namespace app\controllers\order;
use app\controllers\BaseController;
use app\helpers\CommonHelper;
use app\models\Comment;
use app\models\order\Address;
use app\models\order\Item;
use app\models\order\Order;
use app\models\order\OrderHistory;
use app\models\order\OrderIssue;
use app\models\order\OrderPaymentStatusTracking;
use app\models\order\OrderStatusTracking;
use app\models\product\Product;
use app\models\product\Size;
use app\models\product\Stock;
use app\models\shipment\Shipment;
use app\models\User;
use renk\yiipal\components\ExportData;
use renk\yiipal\helpers\ArrayHelper;
use renk\yiipal\helpers\FileHelper;
use renk\yiipal\helpers\Url;
use yii;

class OrderController extends BaseController{


    /**
     * 订单列表
     * @return string
     */
    public function actionIndex(){
        $searchModel = new Order();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams,null,[['approved'=>1]]);
        $models = $dataProvider->getModels();
        $productIds = [];
        foreach($models as $model){
            foreach ($model->items as $item) {
                $productIds[] = $item->product_id;
            }
        }
        $products = Product::find()->where(['in','id',$productIds])->all();
        $products = ArrayHelper::index($products, 'id');

        foreach($models as &$model){
            $model->products = $products;
        }

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * 订单审核列表
     * @return string
     */
    public function actionCreateConfirmList(){
        $searchModel = new Order();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams,null,[['approved'=>0],['not in','status',['cancelled']]]);
        $models = $dataProvider->getModels();
        $productIds = [];
        foreach($models as $model){
            foreach ($model->items as $item) {
                $productIds[] = $item->product_id;
            }
        }
        $products = Product::find()->where(['in','id',$productIds])->all();
        $products = ArrayHelper::index($products, 'id');

        foreach($models as &$model){
            $model->products = $products;
        }

        return $this->render('create-confirm-list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * 订单审核
     * @return string
     */
    public function actionCreateConfirm(){
        $posts = Yii::$app->request->post();
        if(!empty($posts['ids'])){
            $ids = explode(",",$posts['ids']);
            $orderModel = new Order();
            $orderModel->approveOrder($ids);
            return $this->json_output();
        }else{
            return $this->json_output(['status'=>0,'msg'=>'请选择要审核的项目']);
        }
    }

    /**
     * 变更确认列表
     * @return string
     */
    public function actionChangeConfirmList(){
        $searchModel = new Order();
        $conditions = [
            Order::TASK_STATUS_ADDRESS_CHANGED,
            Order::TASK_STATUS_ITEM_CHANGED,
            Order::TASK_STATUS_SHIPPING_METHOD_CHANGED,
            Order::TASK_STATUS_CHANGE_CONFIRMED
        ];
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams,null,[['not in','status',['shipped','cancelled','return_completed','exchange_completed']],["in",'last_track_status',$conditions]]);
        $models = $dataProvider->getModels();
        $productIds = [];
        foreach($models as $model){
            foreach ($model->items as $item) {
                $productIds[] = $item->product_id;
            }
        }
        $products = Product::find()->where(['in','id',$productIds])->all();
        $products = ArrayHelper::index($products, 'id');

        foreach($models as &$model){
            $model->products = $products;
        }

        return $this->render('confirm-list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * 确认变更.
     * @return array
     */
    public function actionChangeConfirm(){
        $posts = Yii::$app->request->post();
        if(!empty($posts['ids'])){
            $ids = explode(",",$posts['ids']);
            $orderModel = new Order();
            $orderModel->changeConform($ids);
            return $this->json_output();
        }else{
            return $this->json_output(['status'=>0,'msg'=>'请选择要确认的项目']);
        }
    }

    /**
     * 订单详情
     * @param $id
     * @return string
     */
    public function actionView($id){
        $order = Order::findOne($id);
        $orderIssue = OrderIssue::find()->where(['and',['order_id'=>$id],['in','issue_status',['pending','processing','wait_confirm']]])->one();
        return $this->render('view', [
            'model' => $order,
            'orderIssue'=>$orderIssue
        ]);
    }

    /**
     * 查看订单历史详情
     * @param $id
     * @return string
     */
    public function actionViewHistory($id){
        $order = OrderHistory::find()->where(['order_id'=>$id])->one();
        return $this->render('view-history', [
            'model' => $order,
        ]);
    }

    /**
     * 配货订单详情
     * @param $id
     * @return string
     */
    public function actionDistributionView($id){
        $order = Order::findOne($id);
        return $this->render('distribution-view', [
            'model' => $order,
        ]);
    }

    /**
     * 订单加急
     * @param $id
     * @return array
     */
    public function actionExpedite($id){
        $order = Order::findOne($id);
        if(empty($order)){
            return $this->json_output(['status'=>0,'msg'=>'处理失败']);
        }else{
            $order->expedited();
            Order::archiveOrder($order->id,'订单加急');
            return $this->json_output();
        }
    }

    /**
     * 订单待定
     * @param $id
     * @return array
     */
    public function actionPause($id){

        if($this->isPost()){
            $posts = Yii::$app->request->post();
            $order = Order::findOne($id);
            if(empty($order)){
                return $this->json_output(['status'=>0,'msg'=>'处理失败']);
            }else{
                $order->paused = $posts['pause'];
                $order->save();
                Order::archiveOrder($order->id,'订单待定');
                return $this->json_output();
            }
        }else{
            $order = Order::findOne($id);
            return $this->renderAjax('//fragment/order-pause',['modal'=>$order]);
        }
    }

    /**
     * 取消待定
     * @param $id
     * @return array
     */
    public function actionPauseResume($id){
        $order = Order::findOne($id);
        if(empty($order)){
            return $this->json_output(['status'=>0,'msg'=>'处理失败']);
        }else{
            $order->paused = 0;
            $order->save();
            Order::archiveOrder($order->id,'取消待定');
            return $this->json_output();
        }
    }

    /**
     * 取消订单
     * @param $id
     * @return array
     */
    public function actionCancel($id){
        $order = Order::findOne($id);
        if(empty($order)){
            return $this->json_output(['status'=>0,'msg'=>'处理失败']);
        }else{
            $items = $order->items;
            foreach($items as $item){
                $item->cancel();
            }
            return $this->json_output();
        }
    }

    /**
     * 发货错误
     * @param $id
     * @return array
     */
    public function actionShipmentWrong($id){
        $order = Order::findOne($id);
        if(empty($order)){
            return $this->json_output(['status'=>0,'msg'=>'处理失败']);
        }else{
            $order->shipmentWrong();
            return $this->json_output();
        }
    }

    /**
     * 恢复订单
     * @param $id
     * @return array
     */
//    public function actionCancelResume($id){
//        $order = Order::findOne($id);
//        if(empty($order)){
//            return $this->json_output(['status'=>0,'msg'=>'处理失败']);
//        }else{
//            $order->status = Item::TASK_STATUS_CANCELLED;
//            $order->save();
//            return $this->json_output();
//        }
//    }


    /**
     * 订单列表页开始处理订单
     */
    public function actionProcess(){
        $posts = Yii::$app->request->post();
        if(!empty($posts['ids'])){
            $ids = explode(",",$posts['ids']);
            $orderModel = new Order();
            $orderModel->processOrder($ids);
            return $this->json_output();
        }else{
            return $this->json_output(['status'=>0,'msg'=>'请选择要处理的项目']);
        }
    }

    /**
     * 订单处理状态跟踪
     * @param $id
     * @return string
     */
    public function actionOrderStatusTracking($id){
        $logs = OrderStatusTracking::find()->with('user')->where(['order_id'=>$id])->orderBy("created_at DESC")->all();
        return $this->renderAjax('//fragment/order-status-tracking',['logs'=>$logs]);
    }

    /**
     * 订单支付状态跟踪
     * @param $id
     * @return string
     */
    public function actionOrderPaymentStatusTracking($id){
        $logs = OrderPaymentStatusTracking::find()->where(['order_id'=>$id])->orderBy("created_at DESC")->all();
        return $this->renderAjax('//fragment/order-payment-status-tracking',['logs'=>$logs]);
    }

    /**
     * 导入物流单号
     * @return string
     */
    public function actionImportShipments(){
        $shipmentModel = new Shipment();
        if ($shipmentModel->load(Yii::$app->request->post()) && $shipmentModel->import()) {
            Yii::$app->session->setFlash('success', '操作成功！');
            return $this->redirect(['import-shipments']);
        }
        return $this->render('import-shipments',[
            'model'=>$shipmentModel
        ]);
    }

    /**
     * 创建订单
     * @return string
     */
    public function actionCreate(){
        $order = new Order(['scenario' => 'create']);
        $address = new Address(['scenario' => 'create']);
        $item = new Item(['scenario' => 'create']);
        if($this->post()){
            $posts = $this->post();
            $skus = $posts['Item']['sku'];
            foreach($skus as $sku){
                $sku = trim($sku);
                $product = Product::find()->where(['sku'=>$sku])->one();
                if(empty($product)){
                    return $this->json_output(['status'=>0,'msg'=>'SKU【'.$sku.'】不存在！']);
                }
            }

            //保存订单
            $order->increment_id = $posts['Order']['increment_id'];
            $order->payment_status = 'processing';
            $order->payment_method = 'paypal_express';
            $order->currency_code = 'USD';
            $order->status = 'pending';
            $order->last_track_status = 'normal';
            $order->customer_id = $this->getCurrentUid();
            $order->grand_total = 0;
            $order->subtotal = 0;
            $order->shipping_method = $posts['Order']['shipping_method'];
            $order->total_item_count = 1;
            $order->source = 'SYS';
            $order->store_id = '2';
            $order->approved = 0;
            $order->uid = $this->getCurrentUid();
            $order->save();
            $orderId = Yii::$app->db->getLastInsertID();
            //保存地址
            $address->load($this->post());
            $address->parent_id = $orderId;
            $address->address_type = 'shipping';
            $address->save();
            //保存Item
            $items = $posts['Item'];
            $itemCount = count($items['sku']);
            for($i=0;$i<$itemCount;$i++){
                $product = Product::find()->where(['sku'=>trim($items['sku'][$i])])->one();
                $item = new Item();
                $item->order_id = $orderId;
                $item->increment_id = $order->increment_id;
                $item->product_id = $product->id;
                $item->sku = $product->sku;
                $item->size_type = $items['size_type'][$i];
                $item->size_original = $items['size_original'][$i];
                $item->size_us = $items['size_us'][$i];
                $item->qty_ordered = $items['qty_ordered'][$i];
                $item->price = $items['price'][$i];
                $order->grand_total += ($item->price*$item->qty_ordered);
                $order->subtotal += $order->grand_total;
                $item->item_type = Item::checkItemType($item, $product);
                $item->item_status = Item::TASK_STATUS_PENDING;
                $item->save();
            }
            $order->save();

            //更新订单类型
            $order = Order::find()->where(['id'=>$orderId])->one();
            $items = $order->items;
            //多个商品=混合单
            if(count($items)>1){
                $order->order_type = Order::ORDER_TYPE_MIXTURE;
            }else{
                $item = $items[0];
                if($item->item_type==Order::ORDER_TYPE_CUSTOM){
                    $order->order_type = Order::ORDER_TYPE_CUSTOM;
                }elseif($item->item_type==Order::ORDER_TYPE_STOCK){
                    $order->order_type = Order::ORDER_TYPE_STOCK;
                }elseif($item->item_type==Order::ORDER_TYPE_TB){
                    $order->order_type = Order::ORDER_TYPE_TB;
                }
            }
            $order->save();


            //保存备注
            $comment = $this->post('comment','');
            $commentModel = new Comment();
            $commentModel->type = 'order';
            $commentModel->subject = 'others';
            $commentModel->target_id = $order->getPrimaryKey();
            $commentModel->content = $comment;
            $commentModel->uid = $this->getCurrentUid();
            $commentModel->visible_uids = '';
            $users = User::getRoleUsers(['admin','Backend-Manage','distribution-permission','order-manager','service','service-manger']);
            if($users){
                foreach($users as $user){
                    $commentModel->visible_uids .= $user->id.',';
                }
            }
            $commentModel->visible_uids = rtrim($commentModel->visible_uids,',');
            $commentModel->save();
            return $this->redirect(['/order/order/create-confirm-list']);
        }

        $sizeAliasList = [];
        $sizes = Size::find()->all();
        $sizeList=ArrayHelper::options($sizes, 'size','size');
        foreach($sizes as $size){
            $alias = explode("\r\n",$size->alias);
            $aliasList = [];
            foreach($alias as $val){
                $aliasList[$val] = $val;
            }
            $sizeAliasList[$size->size] = $aliasList;
        }
        return $this->render('create',[
            'order'=>$order,
            'address'=>$address,
            'item'=>$item,
            'sizeList'=>$sizeList,
            'sizeAliasList'=>$sizeAliasList,
        ]);
    }

    /**
     * 修改物流方式
     * @param $id
     * @return array|string
     */
    public function actionEditShippingMethod($id){
        if($this->isPost()){
            $shippingMethod = Yii::$app->request->post('shipping_method','EUB');
            if($shippingMethod){
                $model = new Order();
                $model->changeShippingMethod($id, $shippingMethod);
                return $this->checkOrderIssueOutput($id);
            }else{
                return $this->json_output(['status'=>0,'msg'=>'处理失败']);
            }
        }else{
            return $this->renderAjax('//fragment/edit-shipping-method',['id'=>$id]);
        }
    }

    /**
     * 修改订单金额
     * @param $id
     * @return array|string
     */
    public function actionEditOrderTotal($id){
        if($this->isPost()){
            $grandTotal = Yii::$app->request->post('grand_total',false);
            if($grandTotal !==false){
                $model = new Order();
                $model->changeGrandTotal($id, $grandTotal);
                return $this->json_output();
            }else{
                return $this->json_output(['status'=>0,'msg'=>'处理失败']);
            }
        }else{
            $model = Order::findOne($id);
            return $this->renderAjax('//fragment/edit-order-total',['id'=>$id,'model'=>$model]);
        }
    }

    /**
     * 修改收货地址
     * @param $id
     * @return array|string
     */
    public function actionEditShippingAddress($id){
        if($this->isPost()){
            $addressModel = Address::find()->where(['id'=>$id])->one();
            if($addressModel->load(Yii::$app->request->post())){
                $addressModel->changeAddress();
                return $this->checkOrderIssueOutput($addressModel->parent_id);
            }else{
                return $this->json_output(['status'=>0,'msg'=>'处理失败']);
            }
        }else{
            $addressModel = Address::find()->where(['parent_id'=>$id,'address_type'=>'shipping'])->one();
            return $this->renderAjax('//fragment/edit-shipping-address',['model'=>$addressModel]);
        }
    }

    /**
     * 直接修改邮寄地址.
     * @param $id
     * @return array|string
     */
    public function actionEditShippingAddressDirectly($id){
        if($this->isPost()){
            $addressModel = Address::find()->where(['id'=>$id])->one();
            if($addressModel->load(Yii::$app->request->post())){
                $addressModel->save();
                return $this->json_output();
            }else{
                return $this->json_output(['status'=>0,'msg'=>'处理失败']);
            }
        }else{
            $addressModel = Address::find()->where(['parent_id'=>$id,'address_type'=>'shipping'])->one();
            return $this->renderAjax('//fragment/edit-shipping-address-directly',['model'=>$addressModel]);
        }
    }

    /**
     * 修改SKU
     * @param $id
     * @return array|string
     */
    public function actionEditOrderSku($id){
        if($this->isPost()){
            $itemModel = Item::find()->where(['id'=>$id])->one();
            $posts = $this->post();
            $sku = trim($posts['Item']['sku']);
            $product = Product::find()->where(['sku'=>$sku])->one();
            if(empty($product)){
                return $this->json_output(['status'=>0,'msg'=>'SKU不存在']);
            }

            if($sku == $itemModel->sku){
                return $this->json_output(['status'=>0,'msg'=>'没有改变SKU']);
            }

            if($sku){
                $itemModel->changeSku($sku);
                return $this->checkOrderIssueOutput($itemModel->order_id);
            }else{
                return $this->json_output(['status'=>0,'msg'=>'处理失败']);
            }
        }else{
            $orderItem = Item::findOne(['id'=>$id]);
            return $this->renderAjax('//fragment/edit-order-product',['model'=>$orderItem,'action'=>'edit-order-sku','field'=>'sku']);
        }
    }

    /**
     * 修改订单数量
     * @param $id
     * @return array|string
     */
    public function actionEditOrderQuantity($id){
        if($this->isPost()){
            $itemModel = Item::findOne(['id'=>$id]);
            $posts = $this->post();
            $qty = trim($posts['Item']['qty_ordered']);
            if($qty == $itemModel->qty_ordered){
                return $this->json_output(['status'=>0,'msg'=>'数量没有改变']);
            }

            if($qty > $itemModel->qty_ordered && $itemModel->order->source != 'SYS'){
                return $this->json_output(['status'=>0,'msg'=>'不能增加数量，只能减少']);
            }

            if($qty){
                $itemModel->changeQuantity($qty);
                return $this->checkOrderIssueOutput($itemModel->order_id);
            }else{
                return $this->json_output(['status'=>0,'msg'=>'处理失败']);
            }
        }else{
            $orderItem = Item::findOne(['id'=>$id]);
            return $this->renderAjax('//fragment/edit-order-product',['model'=>$orderItem,'action'=>'edit-order-quantity','field'=>'qty_ordered']);
        }
    }

    /**
     * 修改订单产品尺码
     * @param $id
     * @return array|string
     */
    public function actionEditOrderProductSize($id){
        if($this->isPost()){
            $itemModel = Item::findOne(['id'=>$id]);
            $posts = $this->post();
            $sizeUs = trim($posts['Item']['size_us']);
            $sizeOriginal = trim($posts['Item']['size_original']);

            if($sizeUs == $itemModel->size_us && $sizeOriginal == $itemModel->size_original){
                return $this->json_output(['status'=>0,'msg'=>'尺码没有变化']);
            }else{
                $itemModel->changeSize($sizeUs,$sizeOriginal);
                return $this->checkOrderIssueOutput($itemModel->order_id);
            }
        }else{
            $orderItem = Item::findOne(['id'=>$id]);
            $sizeAliasList = [];
            $sizes = Size::find()->all();
            $sizeList=ArrayHelper::options($sizes, 'size','size');
            foreach($sizes as $size){
                $alias = explode("\r\n",$size->alias);
                $aliasList = [];
                foreach($alias as $val){
                    $aliasList[$val] = $val;
                }
                $sizeAliasList[$size->size] = $aliasList;
            }
            return $this->renderAjax('//fragment/edit-order-product-size',['model'=>$orderItem,'sizeList'=>$sizeList,'sizeAliasList'=>$sizeAliasList]);
        }
    }

    /**
     * 修改订单刻字
     * @param $id
     * @return array|string
     */
    public function actionEditOrderEngravings($id){
        if($this->isPost()){
            $itemModel = Item::findOne(['id'=>$id]);
            $posts = $this->post();
            $engravings = trim($posts['Item']['engravings']);
            if($engravings == $itemModel->engravings){
                return $this->json_output(['status'=>0,'msg'=>'刻字内容没有改变']);
            }

            $itemModel->changeEngravings($engravings);
            return $this->checkOrderIssueOutput($itemModel->order_id);

        }else{
            $orderItem = Item::findOne(['id'=>$id]);
            return $this->renderAjax('//fragment/edit-order-product',['model'=>$orderItem,'action'=>'edit-order-engravings','field'=>'engravings']);
        }
    }


    /**
     * 修改物流信息
     * @param $id
     * @return array|string
     */
    public function actionEditShippingInfo(){
        $id = $this->post('pk');
        $field = $this->post('name');
        $value = $this->post('value');
        $model = Order::findOne($id);
        if($model){
            $model->{$field} = trim($value);
            $model->has_shipment = 1;
            $model->save();

            $shipment = Shipment::find()->where(['order_id'=>$model->id])->one();
            if(empty($shipment)){
                $shipment = new Shipment();
                $shipment->order_id = $model->id;
                $shipment->uid = $this->getCurrentUid();
                $shipment->shipping_label = $model->shipping_description;
            }
            $shipment->shipping_method = $model->shipping_method;
            $shipment->shipping_number = $model->shipping_track_no;
            $shipment->save();
            Order::archiveOrder($model->id,'修改产品物流信息');
            return $this->json_output();
        }else{
            return $this->json_output(['status'=>0,'msg'=>'处理失败']);
        }
    }

    /**
     * 退货退款
     * @param $id
     * @return array|string
     */
    public function actionReturn($id){
        $order = Order::findOne($id);
        if(empty($order)){
            return $this->json_output(['status'=>0,'msg'=>'处理失败']);
        }

        if($this->isPost()){
            $shippingMethod = $this->post('shipping_method');
            $shippingTrackNo = $this->post('shipping_track_no');
            $itemIds = $this->post('item_ids');

            if(empty($shippingMethod) || empty($shippingTrackNo)){
                return $this->json_output(['status'=>0,'msg'=>'请填写物流信息']);
            }

            if(empty($itemIds)){
                return $this->json_output(['status'=>0,'msg'=>'请选择要退的产品']);
            }

            $order->returnProduct($id, $itemIds,$shippingMethod,$shippingTrackNo);
            return $this->json_output();
        }else{
            $items = Item::findAll(['order_id'=>$order->id]);
            return $this->renderAjax('//fragment/edit-order-return-exchange',['items'=>$items,'order'=>$order,'action'=>'return']);
        }
    }

    /**
     * 退货退款完成
     * @param $id
     * @return array
     */
    public function actionReturnComplete($id){
        $order = Order::findOne($id);
        if(empty($order)){
            return $this->json_output(['status'=>0,'msg'=>'处理失败']);
        }
        $order->returnProductComplete($id);
        return $this->json_output();
    }

    /**
     * 退货换货
     * @param $id
     * @return array|string
     */
    public function actionExchange($id){
        $order = Order::findOne($id);
        if(empty($order)){
            return $this->json_output(['status'=>0,'msg'=>'处理失败']);
        }

        if($this->isPost()){
            $shippingMethod = $this->post('shipping_method');
            $shippingTrackNo = $this->post('shipping_track_no');
            $itemIds = $this->post('item_ids');

            if(empty($shippingMethod) || empty($shippingTrackNo)){
                return $this->json_output(['status'=>0,'msg'=>'请填写物流信息']);
            }

            if(empty($itemIds)){
                return $this->json_output(['status'=>0,'msg'=>'请选择要退换的产品']);
            }

            $order->exchangeProduct($id, $itemIds,$shippingMethod,$shippingTrackNo);
            return $this->json_output();
        }else{
            $items = Item::findAll(['order_id'=>$order->id]);
            return $this->renderAjax('//fragment/edit-order-return-exchange',['items'=>$items,'order'=>$order,'action'=>'exchange']);
        }
    }

    /**
     * 退货换货完成
     * @param $id
     * @return array
     */
    public function actionExchangeComplete($id){
        $order = Order::findOne($id);
        if(empty($order)){
            return $this->json_output(['status'=>0,'msg'=>'处理失败']);
        }
        $order->exchangeComplete($id);
        $this->setFlash('success','换货完成，订单已经初始化，请修改SKU。');
        return $this->json_output(['command'=>['method'=>'redirect','url'=>Url::to(['/order/order/view','id'=>$id])]]);
    }

    /**
     * 取消某个Item
     * @param $id
     * @return array
     */
    public function actionCancelProduct($id){
        $item = Item::findOne($id);
        if(empty($item)){
            return $this->json_output(['status'=>0,'msg'=>'处理失败']);
        }
        $item->cancel($id);
        return $this->json_output();
    }

    /**
     * 修改订单状态为processing
     * @param $id
     * @return array
     */
    public function actionEditOrderPaymentStatus($id){
        $model = Order::findOne($id);
        if($model && in_array($model->payment_status,['paypal_reversed','paypal_canceled_reversal'])){
            $model->payment_status = 'processing';
            $model->save();
            OrderPaymentStatusTracking::track($model);
            return $this->json_output();
        }else{
            return $this->json_output(['status'=>0,'msg'=>'处理失败']);
        }
    }

    /**
     * 定制转库存《=》库存转定制,延后开发
     * @param $id
     * @param string $type
     * @return array
     */
    public function actionChangeItemType($id,$type='stock'){
        $item = Item::findOne($id);
        if(empty($item)){
            return $this->json_output(['status'=>0,'msg'=>'处理失败']);
        }
        $item->changeItemType($id,$type);
        return $this->json_output();
    }

    /**
     * 导出订单
     * @return array
     */
    public function actionExport(){
        $posts = Yii::$app->request->post();
        if(!empty($posts['ids'])){
            $ids = explode(",",$posts['ids']);
            $items = Item::find()->with('product')->with('order')->with('stocks')->with('address')
                ->innerJoin('order','order_item.order_id = order.id')->where(['in','order_id', $ids])->orderBy('order_id ASC')->all();
            $header = $this->createExportHeader();
            $data = $this->formatExportData($items);
            $objExportData = new ExportData($header,$data);
            $objExportData->createExcel($header,$data);
            $path = 'download/orders/'.date("Y-m-d").'/'.'orders-'.date('Y-m-d-H-i-s').'.xls';
            $objExportData->saveFileTo($path);
            return $this->json_output(['data'=>['/'.$path]]);
        }else{
            return $this->json_output(['status'=>0,'msg'=>'请选择要导出的项目']);
        }
    }

    /**
     * 导出的头部
     * @return array
     */
    private function createExportHeader(){
        $header = [
            '编号',
            '订单号',
            'SKU',
            '图片',
            '网站尺码',
            '刻字',
            '数量',
            '收件人姓名（英文）',
            '邮箱',
            '总地址',
            '收件人地址1（英文）',
            '收件人城市',
            '收件人州',
            '收件人邮编',
            '收件人国家',
            '收件人电话',
            '物流方式',
        ];
        return $header;
    }

    /**
     * 格式化要导出的数据
     * @param $data
     * @return array
     */
    private function formatExportData($items){
        $productType = ['none'=>'','men'=>'(男)','women'=>'(女)'];
        $output = [];
        foreach($items as $index=>$item){
            $output[$index][] = $item->order->ext_order_id;
            $output[$index][] = $item->order->increment_id;
            $output[$index][] = $item->sku;
            $files = $item->product->getFiles();
            $filePath = FileHelper::getThumbnailPath($files[0]->file_path, '300x300');
            $filePath = str_replace(urlencode("#"),"#",$filePath);
            $output[$index][] = ['type'=>'image','value'=>'./'.$filePath];
            $output[$index][] = $item->size_original.$productType[$item->size_type];
            $output[$index][] = '刻字: '.html_entity_decode($item->engravings).$productType[$item->engravings_type];
            $output[$index][] = $item->qty_ordered;
            $address = $item->address;
            $output[$index][] = CommonHelper::filterEmptyStr($address->firstname).' '.CommonHelper::filterEmptyStr($address->lastname);
            $output[$index][] = $address->email;
            $street = $address->street? str_replace(["\r\n", "\r", "\n"], ' ', $address->street) : ' ';
            $fullAddress = $street;
            if( CommonHelper::filterEmptyStr($address->company)) {
                $fullAddress .= $address->company;
            }
            if( CommonHelper::filterEmptyStr($address->city)) {
                $fullAddress .= ','.$address->city;
            }
            if( CommonHelper::filterEmptyStr($address->region)) {
                $fullAddress .= ','.$address->region;
            }
            if( CommonHelper::filterEmptyStr($address->postcode)) {
                $fullAddress .= ','.$address->postcode;
            }
            if( CommonHelper::filterEmptyStr($address->country_id)) {
                $fullAddress .= ','.$address->country_id;
            }
            if( CommonHelper::filterEmptyStr($address->telephone)) {
                $fullAddress .= ','.$address->telephone;
            }
            $output[$index][] = $fullAddress;
            $output[$index][] = $street;
            $output[$index][] = CommonHelper::filterEmptyStr($address->city);
            $output[$index][] = CommonHelper::filterEmptyStr($address->region);
            $output[$index][] = CommonHelper::filterEmptyStr($address->postcode);
            $output[$index][] = CommonHelper::filterEmptyStr($address->country_id);
            $output[$index][] = CommonHelper::filterEmptyStr($address->telephone);
            $output[$index][] = $item->order->shipping_method;
        }
        return $output;
    }

    private function checkOrderIssueOutput($order_id){
        $orderIssue = OrderIssue::find()->where(['and',['order_id'=>$order_id],['in','issue_status',['pending','processing','wait_confirm']]])->one();
        if($orderIssue){
            return $this->json_output(
                [
                    'command' => ['method'=>'redirect','url'=>'/order/issue/index?Order[ext_order_id]='.$orderIssue->ext_order_id],
                    'msg'=>'该订单需要联系客户，若已核实，请在联系客户中点击已解决.'
                ]);
        }else{
            $this->json_output();
        }
    }
}