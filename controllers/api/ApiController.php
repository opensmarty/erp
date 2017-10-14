<?php
/**
 * CustomConroller.php
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/5/23
 */

namespace app\controllers\api;


use app\controllers\BaseController;
use app\models\Comment;
use app\models\IpList;
use app\models\order\Address;
use app\models\order\Item;
use app\models\order\Order;
use app\models\order\OrderIssue;
use app\models\order\OrderPaymentStatusTracking;
use app\models\product\Product;
use app\models\product\Size;
use app\models\product\Stock;
use app\models\User;
use app\models\Website;
use Yii;
class ApiController extends BaseController{
    public $enableCsrfValidation = false;
    /**
     * 同步订单
     * @return string
     */
    public function actionSyncOrder(){

        $posts = Yii::$app->request->post();
        if(isset($posts['method']) && $posts['method']=='syncAddress'){
            $this->syncAddress($posts);
            return true;
        }
        if(!isset($posts['magento-order'])){
            return false;
        }
        $xml = simplexml_load_string($posts['magento-order']);
        $json = json_encode($xml);
        $orderRaw = json_decode($json,TRUE);
        $this->saveOrder($orderRaw);
    }

    /**
     * 限制IP
     * @return bool
     */
    public function checkIpAddress(){
        return true;
    }

    /**
     * 同步Magento的地址
     * @return bool
     */
    private function syncAddress($posts){
//        $posts = Yii::$app->request->post();
        if(!isset($posts['order_id'])){
            return false;
        }
        $incrementId = $posts['order_id'];
        $order = Order::find()->where(['increment_id'=>$incrementId])->one();
        if(empty($order)){
            return false;
        }
        $address = Address::find()->where(['parent_id'=>$order->id,'address_type'=>'shipping'])->one();
        if($address){
            if(isset($posts['region_id'])){
                $address->region_id = $posts['region_id'];
            }

            if(isset($posts['fax'])){
                $address->fax = $posts['fax'];
            }

            if(isset($posts['region'])){
                $address->region = $posts['region'];
            }

            if(isset($posts['postcode'])){
                $address->postcode = $posts['postcode'];
            }

            if(isset($posts['lastname'])){
                $address->lastname = $posts['lastname'];
            }

            if(isset($posts['street'])){
                $address->street = $posts['street'];
            }

            if(isset($posts['city'])){
                $address->city = $posts['city'];
            }

            if(isset($posts['email'])){
                $address->email = $posts['email'];
            }

            if(isset($posts['telephone'])){
                $address->telephone = $posts['telephone'];
            }

            if(isset($posts['country_id'])){
                $address->country_id = $posts['country_id'];
            }

            if(isset($posts['firstname'])){
                $address->firstname = $posts['firstname'];
            }

            if(isset($posts['middlename'])){
                $address->middlename = $posts['middlename'];
            }

            if(isset($posts['company'])){
                $address->company = $posts['company'];
            }
        }
        $address->save();
        $this->syncAddressComment($order->id);
        $this->syncOrderIssue($order->id);
    }

    private function syncOrderIssue($orderId){
        $orderIssue = OrderIssue::find()->where(['order_id'=>$orderId])->one();
        if($orderIssue){
            $orderIssue->issue_status = 'wait_confirm';
            $orderIssue->save();
        }
    }

    private function syncAddressComment($orderId){
        $commentModel = new Comment();
        $commentModel->type = 'order';
        $commentModel->subject = Comment::COMMENT_TYPE_DESCRIPTION;
        $commentModel->target_id = $orderId;
        $commentModel->content = '客户修改收货地址';
        $commentModel->uid = -1;
        $commentModel->visible_uids = '';
        $users = User::getRoleUsers(['admin','Backend-Manage','distribution-permission','order-manager','service','service-manger']);
        if($users){
            foreach($users as $user){
                $commentModel->visible_uids .= $user->id.',';
            }
        }
        $commentModel->visible_uids = rtrim($commentModel->visible_uids,',');
        $commentModel->save();
        $order = Order::findOne($orderId);
        if($order->status !=Item::TASK_STATUS_PENDING){
            $order->last_track_status = Order::TASK_STATUS_ADDRESS_CHANGED;
        }
        $order->save();
//        Order::archiveOrder($order->id,'修改收货地址');
    }

    /**
     * 同步Order
     * @param $orderRaw
     */
    private function saveOrder($orderRaw){
        $order = Order::find()->where(['increment_id'=>$orderRaw['increment_id']])->one();
        //如果是complete的订单，直接丢掉，防止重复发货.
        if(strtolower($orderRaw['status'])=='complete'){
            return false;
        }
        if(empty($order)){
            $order = new Order();
            $order->increment_id = $orderRaw['increment_id'];
            $order->payment_status = $orderRaw['status'];
            $order->payment_method = $orderRaw['order_payment']['method'];
            $order->currency_code = $orderRaw['order_currency_code'];
            $order->status = Item::TASK_STATUS_PENDING;
            $order->last_track_status = Order::TASK_STATUS_NORMAL;
            $order->coupon_code = $orderRaw['coupon_code'];
            $order->customer_id = $orderRaw['customer_id'];
            $order->grand_total = $orderRaw['grand_total'];
            $order->subtotal = $orderRaw['subtotal'];
            $order->shipping_track_no = '';
            $order->shipping_description = $orderRaw['shipping_description'];
            $order->shipping_method = $this->getShippingMethod($order->shipping_description);
            $order->total_item_count = $orderRaw['total_item_count'];
            $order->from = isset($orderRaw['jeulia_ismobile'])&&$orderRaw['jeulia_ismobile']=='1'?'mobile':'pc';
            $website = Website::find()->where(['security_key'=>trim($orderRaw['apikey'])])->one();
            $order->store_id = $website->id;
            $order->source =$website->country;
        }
        $oldPaymentStatus = $order->payment_status;
        $order->payment_status = $orderRaw['status'];
        if($order->payment_method == 'purchaseorder' && isset($orderRaw['order_payment']['method']) && $orderRaw['order_payment']['method'] !='purchaseorder'){
            $order->payment_method = $orderRaw['order_payment']['method'];
        }

        $isNewRecord = $order->getIsNewRecord();
        $order->save();
        //新订单同步数据
        //if($isNewRecord || empty($order->items)){
        if($isNewRecord){
            if($isNewRecord){
                $orderId = Yii::$app->db->getLastInsertID();
            }else{
                $orderId = $order->id;
            }
            if(isset($orderRaw['order_items']['item_id'])){
                $orderRaw['order_items'] = array($orderRaw['order_items']);
            }
            $this->saveItems($orderRaw,$orderId);
            $this->updateOrderType($orderId);
            $this->saveOrderAddress($orderRaw, $orderId,$order);

            //新订单支付成功，直接发送通知邮件
            if($order->payment_status=='processing'){
                //发送通知邮件
                if($order->source == 'us'){
                    $this->sendInformEmail($order->id);
                }
            }

            //如果混合单都是虚拟单，全部发货，则直接标记为已经发货
            $items = $order->items;
            if(!empty($items) && is_array($items)){
                $shipped = true;
                foreach($items as $item){
                    if($item->item_status != Item::TASK_STATUS_SHIPPED){
                        $shipped = false;
                    }
                }

                if($shipped){
                    $order->status = Item::TASK_STATUS_SHIPPED;
                    $order->save();
                }
            }

        }else{
            if($oldPaymentStatus != 'processing' && $order->payment_status=='processing'){
                $this->updateItemType($order);
                //订单状态改变时，更新ERP的订单状态
                $this->updateOrderType($order->id);
                //发送通知邮件
                if($order->source == 'us'){
                    $this->sendInformEmail($order->id);
                }
            }
        }
        //只有在状态变化的时候才做记录
        if($isNewRecord || ($oldPaymentStatus !=$order->payment_status)){
            OrderPaymentStatusTracking::track($order);
        }
        return $order->getPrimaryKey();
    }

    /**
     * 同步订单Items
     * @param $orderRaw
     * @param $order_id
     */
    private function saveItems($orderRaw,$order_id){
        foreach($orderRaw['order_items'] as $data){
            $item = new Item();
            $item->order_id = $order_id;
            $item->increment_id = $orderRaw['increment_id'];
            $product = Product::find()->where(['sku'=>rtrim($data['sku'],'-A')])->one();
            $item->product_id = $product->id;
            $item->sku = rtrim($data['sku'],'-A');
            //虚拟产品直接设置为发货状态
            if($product->type=='virtual'){
                $item->item_status = Item::TASK_STATUS_SHIPPED;
            }else{
                $item->item_status = Item::TASK_STATUS_PENDING;
            }
            $item->price = $data['price'];
            //Jeulia专属代码
            if(!isset($data['product_options_serialize'])){
                $data['product_options_serialize'] = serialize([]);
            }
            $productOptions = unserialize($data['product_options_serialize']);
            $productOptions = $this->getProductOptions($productOptions);
            $item->size_type = 'none';
            $item->size_us = '0';
            if(isset($productOptions['size']['type'])){
                $item->size_type = $productOptions['size']['type'];
                $item->size_us = $productOptions['size']['value'];
                $item->size_original = $productOptions['size']['size_original'];

            }
            if(isset($productOptions['engravings']['type'])){
                $item->engravings_type = $productOptions['engravings']['type'];
                $item->engravings = $productOptions['engravings']['value'];
                
                //API刻字BUG。有刻字label没有Value，API里也判断成了有刻字
                //$item->has_engravings = 1;
                if(empty($item->engravings)){
                    $item->has_engravings=0;
                }else{
                    $item->has_engravings=1;
                }
            }

            $item->qty_ordered = $data['qty_ordered'];
            if($item->sku == 'SPO-0526' || $product->type=='virtual'){
                $item->qty_ordered = 1;
            }
            $item->item_type = Item::checkItemType($item, $product);
            $item->product_options = json_encode($data['product_options_serialize']);
            $item->save();

            //如果是库存款，减少对应的库存
            if($item->item_type == 'stock' && strtolower($orderRaw['status'])=='processing'){
                Stock::reduceStocks($product, $item->qty_ordered,$item->size_us,$item->size_type);
            }
        }
    }

    /**
     * 订单支付后更新产品状态
     * @param $order
     */
    private function updateItemType($order){
        $items = $order->items;
        foreach($items as $item){
            $product = Product::find()->where(['sku'=>$item->sku])->one();
            $item->item_type = Item::checkItemType($item, $product);
            $item->save();
            //如果是库存款，减少对应的库存
            if($item->item_type == 'stock' && strtolower($order->payment_status)=='processing'){
                Stock::reduceStocks($product, $item->qty_ordered,$item->size_us,$item->size_type);
            }
        }
    }

    /**
     * 根据Items更新Order类型
     * @param $orderId
     */
    private function updateOrderType($orderId){
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
                //完成支付后，更新订单状态
                if($item->item_status == Item::TASK_STATUS_SHIPPED && $order->payment_status=='processing'){
                    $order->status = Item::TASK_STATUS_SHIPPED;
                    $order->process_at = time();
                    $order->shipped_at = time();
                }
            }elseif($item->item_type==Order::ORDER_TYPE_TB){
                $order->order_type = Order::ORDER_TYPE_TB;
            }
        }
        $order->save();
    }


    /**
     * 同步订单地址
     * @param $orderRaw
     * @param $orderId
     */
    private function saveOrderAddress($orderRaw,$orderId,$order){
        $orderAddressList = $orderRaw['order_address'];
        
        if(isset($orderAddressList['entity_id'])){
            $orderAddressList['address_type'] = 'shipping';
            $_orderAddressList = $orderAddressList;
            unset($orderAddressList);
            $orderAddressList[1] = $_orderAddressList;
        }
        
        foreach($orderAddressList as $orderAddress){
            unset($orderAddress['entity_id']);
            $orderAddress['parent_id'] = $orderId;
            $address = new Address();
            $address->load($orderAddress,'');
            $address->save();
        }

        //非美国地区的加急物流方式改发DHL。--林霞
        if($orderAddressList[1]['country_id']!="US" && $order->shipping_method == 'UPS'){
            $order->shipping_method = 'DHL';
            $order->save();

            $commentModel = new Comment();
            $commentModel->type = 'order';
            $commentModel->subject = Comment::COMMENT_TYPE_CHANGE_SHIPPING_METHOD;
            $commentModel->target_id = $orderId;
            $commentModel->content = '非美国地区的加急物流方式改发DHL';
            $commentModel->uid = -1;
            $commentModel->visible_uids = '';
            $users = User::getRoleUsers(['admin','Backend-Manage','distribution-permission','order-manager','service','service-manger']);
            if($users){
                foreach($users as $user){
                    $commentModel->visible_uids .= $user->id.',';
                }
            }
            $commentModel->visible_uids = rtrim($commentModel->visible_uids,',');
            $commentModel->save();
        }
    }

    private function getEngravingsFromOptions($options){
        $output = [];
        foreach($options as $k=>$option){
            $label = strtolower($option['label']);
            $label = trim($label," \t\n\r\0\x0B");       
            if($engravingsType = $this->checkEngravingsType($label)){
                $output['engravings'][$k]['type'] = $engravingsType;
                $output['engravings'][$k]['value'] = $option['value'];
            }
        }
        return $output;
    }
    
    /**
     * 获取产品属性.
     * @param $productOptions
     * @return array
     */
    private function getProductOptions($productOptions){
        $output = ['size'=>[],'engravings'=>[]];
        $engravings = [];
        if(isset($productOptions['options'])){
            foreach($productOptions['options'] as $option){
                $label = strtolower($option['label']);
                $label = trim($label," \t\n\r\0\x0B");
                if($engravingsType = $this->checkEngravingsType($label)){
                    $output['engravings']['type'] = $engravingsType;
                    $engravings[$engravingsType] = $option['value'];
                    $output['engravings']['value'] = $option['value'];
                    $engravings[$engravingsType] = $option['value'];
                }elseif($sizeType = $this->checkSizeType($label)){
                    $output['size']['type'] = $sizeType;
                    $output['size']['value'] = Size::getSizeByAlias($option['value']);
                    $output['size']['size_original'] = $option['value'];
                }
            }
        }
        //传过来的数据不能区分刻字的男女款，用尺码区分.
        if(isset($output['engravings']['type'])){
            if(isset($output['size']['type'])){
                $output['engravings']['type']= $output['size']['type'];
                if(isset($engravings[$output['size']['type']])){
                    $output['engravings']['value']= $engravings[$output['size']['type']];
                }
            } else {
                //直接把刻字内容拼接字符串放进去
                $engravings = $this->getEngravingsFromOptions($productOptions['options']);
                $engravingStr = [];
                foreach ($engravings['engravings'] as $engraving){
                    $engravingStr[] = $engraving['type'].':['.$engraving['value'].']';
                }
                $engravingStr = join(',',$engravingStr);
                $output['engravings']['value']= $engravingStr;
            }
        }
        return $output;
    }

    /**
     * 检车刻字类型
     * @param $label
     * @return bool|int|string
     */
    private function checkEngravingsType($label){
        $sizes = Yii::$app->params['engravings'];
        foreach($sizes as $engravings=>$alias){
            if($this->isStrMatchArray($label, $alias) !== false){
                return $engravings;
            }
        }
        return false;
    }

    /**
     * 检查尺码类型
     * @param $label
     * @return int|string
     */
    private function checkSizeType($label){
        $sizes = Yii::$app->params['size'];
        $sizeType = 'none';
        foreach($sizes as $size=>$alias){
            if($this->isStrMatchArray($label, $alias) !== false){
                $sizeType = $size;
                break;
            }
        }
        return $sizeType;
    }

    /**
     * 检查字符创是否匹配数组元素(用来检测尺码和刻字).
     * @param $array
     * @param $str
     * @return bool
     */
    private function isStrMatchArray($str,$array){
        foreach($array as $item){
            $str = str_replace("'s",'',$str);
            if(stripos($str,$item) !== false){
                return true;
            }
        }
        return false;
    }


    /**
     * 获取物流方式
     * @param $shippingDesc
     * @return string
     */
    private function getShippingMethod($shippingDesc)
    {
        if (strripos(strtolower($shippingDesc), 'dhl') !== false) {
            return 'DHL';
        } else if (strripos(strtolower($shippingDesc), 'ups') !== false) {
            return 'UPS';
        } else if (strripos(strtolower($shippingDesc), 'aramex') !== false) {
            return 'ARAMEX';
        } else {
            return 'EUB';
        }
    }


    /**
     * 确认通知邮件的类型
     * @param $order
     * @return string
     */
    private function checkMailType($order){
        $items = $order->items;
        $mailNo = '';
        //定制单邮件
        if($order->order_type == Order::ORDER_TYPE_CUSTOM){
            $mailNo = 'custom';
        }
        //淘宝单邮件
        elseif($order->order_type == Order::ORDER_TYPE_TB){
            $mailNo = 'taobao';
        }
        //库存单邮件
        elseif($order->order_type == Order::ORDER_TYPE_STOCK){
            $item = $items[0];
            $stocks = $item->product->getProductStocks();
            if(empty($stocks)){
                $isStockItem = false;
            }else{
                $sizeInfo = Size::find()->where(['size'=>$item->size_us])->one();
                $sizeId = 0;
                if(!empty($sizeInfo)){
                    $sizeId = $sizeInfo->id;
                }
                $isStockItem = isset($stocks[$item->size_type][$sizeId])&&$stocks[$item->size_type][$sizeId]['actual_total']>=0?true:false;
            }

            //有实际库存
            if($isStockItem){
                //有刻字
                if($item->has_engravings == 1){
                    $mailNo = 'stock_engravings';
                }
                //没有刻字
                else{
                    $mailNo = 'stock_no_engravings';
                }
            }
            //虚拟库存
            else{
                $mailNo = 'virtual_stock';
            }

        }
        //混合单
        elseif($order->order_type == Order::ORDER_TYPE_MIXTURE){
            $mailNo = 'mixture_custom';
            $types = [];
            foreach($items as $item){
                //混合单中有定制
                if($item->item_type==Order::ORDER_TYPE_CUSTOM){
                    $types[] = 'mixture_custom';
                    break;
                }
                if($item->item_type == Order::ORDER_TYPE_TB){
                    $types[] = 'mixture_taobao';
                }

                if($item->item_type == Order::ORDER_TYPE_STOCK && !in_array('mixture_taobao',$types)){
                    $stocks = $item->product->getProductStocks();
                    if(empty($stocks)){
                        $isStockItem = false;
                    }else{
                        $sizeInfo = Size::find()->where(['size'=>$item->size_us])->one();
                        $sizeId = 0;
                        if(!empty($sizeInfo)){
                            $sizeId = $sizeInfo->id;
                        }
                        $isStockItem = isset($stocks[$item->size_type][$sizeId])&&$stocks[$item->size_type][$sizeId]['actual_total']>=0?true:false;
                    }

                    //有实际库存
                    if($isStockItem){
                        //有刻字
                        if($item->has_engravings == 1){
                            $types[] = 'mixture_stock_engravings';
                        }
                        //没有刻字
                        else{
                            $types[] = 'mixture_stock_no_engravings';
                        }
                    }
                    //虚拟库存
                    else{
                        $types[] = 'mixture_virtual_stock';
                    }
                }
            }

            //混合单定制
            if(in_array('mixture_custom',$types)){
                $mailNo = 'mixture_custom';
            }
            //混合单淘宝
            elseif(in_array('mixture_taobao',$types)){
                $mailNo = 'mixture_taobao';
            }
            //混合单库存款+虚拟库存（正在补的）
            elseif(in_array('mixture_virtual_stock',$types)){
                $mailNo = 'mixture_virtual_stock';
            }
            //混合单库存款+有刻字
            elseif(in_array('mixture_stock_engravings',$types)){
                $mailNo = 'mixture_stock_engravings';
            }
            //混合单库存款+无刻字
            else{
                $mailNo = 'mixture_stock_no_engravings';
            }
        }

        return $mailNo;
    }

    /**
     * 发送通知邮件
     * @param $orderId
     */
//    public function actionSendInformEmail($orderId){
    private function sendInformEmail($orderId){
        $order = Order::findOne($orderId);
        $mailType = $this->checkMailType($order);
        $tpl = 'custom';
        switch($mailType){
            case 'custom':
            case 'mixture_custom':
            case 'virtual_stock':
            case 'mixture_virtual_stock':
                $tpl = 'custom';
                break;
            case 'taobao':
            case 'mixture_taobao':
                $tpl = 'taobao';
                break;
            case 'stock_engravings':
            case 'mixture_stock_engravings':
                $tpl = 'stock_lettering';
                break;
            case 'stock_no_engravings':
            case 'mixture_stock_no_engravings':
                $tpl = 'stock';
                break;
        }
        $website = Website::findOne($order->store_id);
        $url = $website->url.'/checkout/NewOrderEmail?order_id='.$order->increment_id.'&tpl='.$tpl;
        file_get_contents($url);
//        $post_data = array(
//            "order_id" => $order->increment_id,
//            'tpl' => $tpl,
//        );
//
//        try {
//            $ch = curl_init();
//            curl_setopt($ch, CURLOPT_URL, $url);
//            curl_setopt($ch, CURLOPT_HEADER, 0);
//            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//            curl_setopt($ch, CURLOPT_POST, 1);
//            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
//
//            $result = curl_exec($ch);
//            curl_exec($ch);
//        } catch(Exception $e) {
//        }
    }

    public function actionSyncIp($location='xian'){
        $ip = $_SERVER['REMOTE_ADDR'];
        $model = IpList::findOne(['location'=>$location]);
        if($model && $model->ip != $ip){
            $model->ip = $ip;
            $model->save();
        }
    }
}