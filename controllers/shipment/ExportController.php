<?php
/**
 * ExportOrderController.php 
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/6/20
 */
namespace app\controllers\shipment;
use app\controllers\BaseController;
use app\helpers\CommonHelper;
use app\models\order\Item;
use app\models\order\Order;
use app\models\product\Product;
use renk\yiipal\components\ExportData;
use yii\helpers\ArrayHelper;
use yii;

class ExportController extends BaseController{

    /**
     * 物流管理订单列表
     * @return string
     */
    public function actionOrderList(){
        $searchModel = new Order();
        $exportConditions = [
            ['in','payment_status',['processing','complete']],
            ['in','status',['pending','waiting_shipped','pick_waiting','processing','pick_waiting','picking','waiting_production','in_production','waiting_accept','product_passed','pending_purchase','purchase','purchase_completed']],
            ['approved'=>'1','blocked'=>'0']
        ];
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams,null,$exportConditions);
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

    private function getItemsTotal($order){
        $total = 0;
        foreach($order->items as $item){
            $item_status = ['shipped','cancelled','return_part_completed'];
            if(in_array($item->item_status,$item_status)){
                continue;
            }
            $total += $item->qty_ordered;
        }
        return $total;
    }

    /**
     * 导出DHL模板
     * @return array
     */
    public function actionExportDhlTpl(){
        $posts = Yii::$app->request->post();
        if(!empty($posts['ids'])){
            $ids = explode(",",$posts['ids']);
            $orderModel = new Order();
            $orders = $orderModel->find()->with('items')->with('address')->where(['in','id',$ids])->andWhere(['shipping_method'=>'DHL'])->all();
            $validOrders = [];
            foreach($orders as $order){
                if($this->canExportDhl($order)){
                    $validOrders[] = $order;
                }
            }
            $header = $this->createExportHeader();
            $data = $this->formatExportData($orders);
            $objExportData = new ExportData($header,$data);
            $objExportData->createExcel($header,$data,20,'快件信息（不包含发件人)');
            $path = 'download/shipment/template/'.date("Y-m-d").'/'.'orders-'.date('Y-m-d-H-i-s').'-DHL.xls';
            $objExportData->saveFileTo($path);
            return $this->json_output(['data'=>['/'.$path]]);

            return $this->json_output();
        }else{
            return $this->json_output(['status'=>0,'msg'=>'请选择要导出的DHL物流订单']);
        }
    }

    /**
     * 判断是否可以导出DHL模板
     * @param $order
     * @return bool
     */
    private function canExportDhl($order){
        if($order->order_type == 'stock'){
            return true;
        }

        //定制单必须是等待发货或者等待验收才可以导出
        if($order->order_type == 'custom'){
            $flag = true;
            foreach($order->items as $item){
                if(!in_array($item->item_status,['waiting_shipped','waiting_accept'])){
                    $flag = false;
                }
            }
            return $flag;
        }

        //淘宝单必须是等待发货才可导出
        if($order->order_type == 'taobao'){
            $flag = true;
            foreach($order->items as $item){
                if(!in_array($item->item_status,['waiting_shipped'])){
                    $flag = false;
                }
            }
            return $flag;
        }

        //混合单必须是单内每个产品都可以导出时才可以导出
        if($order->order_type == 'mixture'){
            $flag = true;
            foreach($order->items as $item){
                if($item->item_type == 'custom' && !in_array($item->item_status,['waiting_shipped','waiting_accept'])){
                    $flag = false;
                }
                if($item->item_type == 'taobao' && !in_array($item->item_status,['waiting_shipped'])){
                    $flag = false;
                }
            }
            return $flag;
        }
    }

    /**
     * 导出EUB模板
     * @return array
     */
    public function actionExportEubTpl(){
        $posts = Yii::$app->request->post();
        if(!empty($posts['ids'])){
            $ids = explode(",",$posts['ids']);
            $orderModel = new Order();
            $orders = $orderModel->find()->with('items')->with('address')->where(['in','id',$ids])->andWhere(['shipping_method'=>'EUB'])->all();
            $header = $this->createExportEubHeader();
            $data = $this->formatExportEubData($orders);
            $objExportData = new ExportData($header,$data);
            $objExportData->createExcel($header,$data,20,'订单信息');

            $header = $this->createExportEubHeaderSheet2();
            $data = $this->formatExportEubDataSheet2($orders);
            $objExportData->createExcel($header,$data,20,'SKU列表',1);
            $objExportData->setActiveSheetIndex(0);
            $path = 'download/shipment/template/'.date("Y-m-d").'/'.'orders-'.date('Y-m-d-H-i-s').'-EUB.xls';
            $objExportData->saveFileTo($path);
            return $this->json_output(['data'=>['/'.$path]]);

            return $this->json_output();
        }else{
            return $this->json_output(['status'=>0,'msg'=>'请选择要导出的DHL物流订单']);
        }
    }

    private function createExportEubHeader(){
        $header = [
            '订单号',
            '商品交易号',
            '商品SKU',
            '数量',
            '收件人姓名',
            '收件人地址1',
            '收件人地址2',
            '收件人地址3',
            '收件人城市',
            '收件人州',
            '收件人邮编',
            '收件人国家',
            '收件人电话',
            '收件人电子邮箱',
            '自定义信息1',
            '备注',
            '来源',
            '寄件地址',
            '发货地址',
            '业务类型',
            '增值服务',
        ];
        return $header;
    }

    private function createExportEubHeaderSheet2(){
        $header = [
            'SKU编号',
            '商品中文名称',
            '商品英文名称',
            '重量（3位小数）',
            '报关价格(整数,',
            '原寄地',
            '保存至系统SKU',
            '税则号',
            '销售链接',
        ];
        return $header;
    }
    /**
     * 格式化要导出的数据
     * @param $data
     * @return array
     */
    private function formatExportEubDataSheet2($orders){
        $output = [];
        foreach($orders as $index=>$order){
            if(!$this->canExport($order))continue;
            $output[$index][] = $order->ext_order_id;
            $output[$index][] = sprintf('首饰   序号：%s', $order->ext_order_id);
            $output[$index][] = 'jewelry';
            $output[$index][] = '0.195';
            $output[$index][] = '10';
            $output[$index][] = 'CN';
            $output[$index][] = '序号:';
            $output[$index][] = '';
            $output[$index][] = '';
        }
        return $output;
    }

    /**
     * 格式化要导出的数据
     * @param $data
     * @return array
     */
    private function formatExportEubData($orders){
        $output = [];
        foreach($orders as $index=>$order){
            if(!$this->canExport($order))continue;
            $address = $order->address;
            $output[$index][] = $order->ext_order_id;
            $output[$index][] = '';
            $output[$index][] = $order->ext_order_id;
            $output[$index][] = $this->getItemsTotal($order);
            $output[$index][] = CommonHelper::filterEmptyStr($address->firstname).' '.CommonHelper::filterEmptyStr($address->lastname);
            //地址第一行
            $street = $address->street? str_replace(["\r\n", "\r", "\n"], ' ', $address->street) : '';
            if( CommonHelper::filterEmptyStr($address->company)) {
                $street .= $address->company;
            }
            $output[$index][] = $street;

            //地址第二行
            $output[$index][] = '';

            //地址第三行
            $output[$index][] = '';

            //收件城市
            $output[$index][] = $address->city;
            $output[$index][] = $address->region;
            $output[$index][] = $address->postcode;
            $output[$index][] = $address->country_id;
            $output[$index][] = CommonHelper::filterEmptyStr($address->telephone);

            $output[$index][] = '';
            $output[$index][] = '';
            $output[$index][] = '';
            $output[$index][] = '';
            $output[$index][] = '';
            $output[$index][] = '';
            $output[$index][] = '';
            $output[$index][] = '';
        }
        return $output;
    }

    /**
     * 导出的头部
     * @return array
     */
    private function createExportHeader(){
        $header = [
            '参考信息',
            '收件人',
            '收件人公司',
            '地址第1行',
            '地址第2行',
            '地址第3行',
            '收件城市',
            '收件邮编',
            '二字码',
            '收件电话',
            '手机',
            '快件内容',
            '重量',
            '箱数',
            '产品',
            '申报价值',
            '产品条目数量',
            '应纳关税标识',
            '出口许可证号',
            '出口税号',
            '证书号',
            '进口许可证',
            '进口税号',
            '贸易条款',
            '出口原因',
            '收件人纳税号',
            '发件人纳税号',
            '收件人国家税码',
            '件数',
            '件数单位',
            '单件重量',
            '单件单位',
            '小数点后位数',
            '实际体积重',
            '长',
            '宽',
            '高',
            '货币单位',
            '价格小数点后位值',
            '单价',
            '原产地',
            '货物描述',
            '1',
            '2',
            '3',
            '4',
            '5',
            '6',
            '7',
            '8',
            '9',
            '10',
            '11',
            '12',
            '13',
            '14',
        ];
        return $header;
    }

    /**
     * 格式化要导出的数据
     * @param $data
     * @return array
     */
    private function formatExportData($orders){
        $output = [];
        foreach($orders as $index=>$order){
            if(!$this->canExport($order))continue;
            $address = $order->address;
            $output[$index][] = $order->ext_order_id;
            $output[$index][] = CommonHelper::filterEmptyStr($address->firstname).' '.CommonHelper::filterEmptyStr($address->lastname);
            $output[$index][] = CommonHelper::filterEmptyStr($address->firstname).' '.CommonHelper::filterEmptyStr($address->lastname);

            //地址第一行
            $street = $address->street? str_replace(["\r\n", "\r", "\n"], ' ', $address->street) : '';
            if( CommonHelper::filterEmptyStr($address->company)) {
                $street .= $address->company;
            }
            $output[$index][] = $street;

            //地址第二行
            $cityRegion = CommonHelper::filterEmptyStr($address->city).','.CommonHelper::filterEmptyStr($address->region).','.CommonHelper::filterEmptyStr($address->postcode);
            $output[$index][] = $cityRegion;

            //地址第三行
            $output[$index][] = '';

            //收件城市
            $output[$index][] = $address->city;
            $output[$index][] = $address->postcode;
            $output[$index][] = $address->country_id;
            $output[$index][] = CommonHelper::filterEmptyStr($address->telephone);
            $output[$index][] = '';
            $output[$index][] = 'ALLOY RING';
            $output[$index][] = '0.5';
            $output[$index][] = '1';
            $output[$index][] = 'P';
            $totalItemCount = $this->getItemsTotal($order);
            $value = $totalItemCount * 20;
            $output[$index][] = $value;
            $output[$index][] = '1';
            $output[$index][] = 'Y';
            $output[$index][] = '';
            $output[$index][] = '';
            $output[$index][] = '';
            $output[$index][] = '';
            $output[$index][] = 'DDD';
            $output[$index][] = 'DDU';
            $output[$index][] = '';
            $output[$index][] = '';
            $output[$index][] = '';
            $output[$index][] = '';
            $output[$index][] = $totalItemCount;
            $output[$index][] = 'PCS';
            $output[$index][] = '';
            $output[$index][] = '';
            $output[$index][] = '';
            $output[$index][] = '';
            $output[$index][] = '';
            $output[$index][] = '';
            $output[$index][] = '';
            $output[$index][] = 'USD';
            $output[$index][] = '1';
            $output[$index][] = '20';
            $output[$index][] = 'CN';
            $output[$index][] = 'ALLOY RING';
            $output[$index][] = '';
            $output[$index][] = '';
            $output[$index][] = '';
            $output[$index][] = '';
            $output[$index][] = '';
            $output[$index][] = '';
            $output[$index][] = '';
            $output[$index][] = '';
            $output[$index][] = '';
            $output[$index][] = '';
            $output[$index][] = '';
            $output[$index][] = '';
            $output[$index][] = '';
            $output[$index][] = '';

        }
        return $output;
    }

    /**
     *
     * @param $order
     * @return bool
     */
    private function canExport($order){
        $statusCanExports = ['pending','waiting_shipped','pick_waiting','processing','pick_waiting','picking','waiting_production','in_production','waiting_accept','product_passed','pending_purchase','purchase','purchase_completed'];
        if(
            in_array($order->payment_status,['processing','complete'])
            &&
            in_array($order->status,$statusCanExports)
        ){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 导出Ups模板
     * @return array
     */
    public function actionExportUpsTpl(){
        $posts = Yii::$app->request->post();
        if(!empty($posts['ids'])){
            $ids = explode(",",$posts['ids']);
            $orderModel = new Order();
            $orders = $orderModel->find()->with('items')->with('address')->where(['in','id',$ids])->andWhere(['shipping_method'=>'UPS'])->all();
            $validOrders = [];
            foreach($orders as $order){
                //UPS和DHL的时效一样
                if($this->canExportDhl($order)){
                    $validOrders[] = $order;
                }
            }
            $header = $this->createExportUpsHeader();
            $data = $this->formatExportUpsData($orders);
            $objExportData = new ExportData($header,$data);
            $objExportData->createExcel($header,$data,20,'IMPORT');
            $path = 'download/shipment/template/'.date("Y-m-d").'/'.'orders-'.date('Y-m-d-H-i-s').'-UPS.csv';
            $objExportData->saveFileTo($path,'CSV');
            return $this->json_output(['data'=>['/'.$path]]);

            return $this->json_output();
        }else{
            return $this->json_output(['status'=>0,'msg'=>'请选择要导出的UPS物流订单']);
        }
    }

    /**
     * 导出的头部
     * @return array
     */
    private function createExportUpsHeader(){
        $header = [
            'CompanyName',
            'Attention',
            'Address1',
            'Address2',
            'Address3',
            'Country',
            'Postcode',
            'City',
            'State',
            'Telephone',
            'ServiceType',
            'Pkgtype',
            'GoodsDesc',
            'PkgNo',
            'Weight',
            'Freight',
            'DutyTax',
            'Reference1',
            'Reference2',
            'Invoiceornot',
            'OtherDoc',
            'Insurance',
            'Intgoodesdes',
            'Unit',
            'Price',
            'Measurement',
            'OriginCountry',
            'Currency',
            'Intlcurrency',
            'Freight',
            'Insurance',
            'AdditionalComment',
            'HSCODE',
        ];
        return $header;
    }

    /**
     * 格式化要导出的数据
     * @param $data
     * @return array
     */
    private function formatExportUpsData($orders){
        $output = [];
        foreach($orders as $index=>$order){
            if(!$this->canExport($order))continue;
            $address = $order->address;
            $company = CommonHelper::filterEmptyStr($address->company);
            $output[$index][] = $company?$company:' ';
            $output[$index][] = CommonHelper::filterEmptyStr($address->firstname).' '.CommonHelper::filterEmptyStr($address->lastname);
            //地址第一行
            $street = $address->street? str_replace(["\r\n", "\r", "\n"], ' ', $address->street) : '';
            $output[$index][] = str_replace(",",' ',$street);
            //地址第二行
            $cityRegion = CommonHelper::filterEmptyStr($address->city).' '.CommonHelper::filterEmptyStr($address->region).' '.CommonHelper::filterEmptyStr($address->postcode);
            $output[$index][] = $cityRegion;

            //地址第三行
            $output[$index][] = '';
            $output[$index][] = $address->country_id;
            $output[$index][] = $address->postcode;
            //收件城市
            $output[$index][] = $address->city;
            $output[$index][] = CommonHelper::filterEmptyStr($this->getRegionShortName($address->region));
            $output[$index][] = CommonHelper::filterEmptyStr($address->telephone);
            $output[$index][] = 'SV';
            $output[$index][] = 'CP';
            $output[$index][] = 'ALLOY RING';
            $output[$index][] = '1';
            $output[$index][] = '0.5';
            $output[$index][] = 'SHP';
            $output[$index][] = 'REC';
            $output[$index][] = $order->ext_order_id;
            $output[$index][] = '';
            $output[$index][] = '1';
            $output[$index][] = '0';
            $output[$index][] = '';
            $output[$index][] = 'ALLOY RING';
            $output[$index][] = $this->getItemsTotal($order);
            $output[$index][] = 100;
            $output[$index][] = 'PCS';
            $output[$index][] = 'CN';
            $output[$index][] = 'USD';
            $output[$index][] = 'USD';
            $output[$index][] = '';
            $output[$index][] = '';
            $output[$index][] = '';
            $output[$index][] = '';
        }
        return $output;
    }

    private function getRegionShortName($region){
        $region = strtolower($region);
        $regionMap = [
            "alabama"=>"AL",
            "alaska"=>"AK",
            "arizona"=>"AZ",
            "arkansas"=>"AR",
            "california"=>"CA",
            "colorado"=>"CO",
            "connecticut"=>"CT",
            "delaware"=>"DE",
            "florida"=>"FL",
            "georgia"=>"GA",
            "hawaii"=>"HI",
            "idaho"=>"ID",
            "illinois"=>"IL",
            "indiana"=>"IN",
            "iowa"=>"IA",
            "kansas"=>"KS",
            "kentucky"=>"KY",
            "louisiana"=>"LA",
            "maine"=>"ME",
            "maryland"=>"MD",
            "massachusetts"=>"MA",
            "michigan"=>"MI",
            "minnesota"=>"MN",
            "mississippi"=>"MS",
            "missouri"=>"MO",
            "montana"=>"MT",
            "nebraska"=>"NE",
            "nevada"=>"NV",
            "new hampshire"=>"NH",
            "new jersey"=>"NJ",
            "new mexico"=>"NM",
            "new york"=>"NY",
            "north carolina"=>"NC",
            "north dakota"=>"ND",
            "ohio"=>"OH",
            "oklahoma"=>"OK",
            "oregon"=>"OR",
            "pennsylvania"=>"PA",
            "rhode island"=>"RL",
            "south carolina"=>"SC",
            "south dakota"=>"SD",
            "tennessee"=>"TN",
            "texas"=>"TX",
            "utah"=>"UT",
            "vermont"=>"VT",
            "virginia"=>"VA",
            "washington"=>"WA",
            "west virginia"=>"WV",
            "wisconsin"=>"WI",
            "wyoming"=>"WY"
        ];
        if(isset($regionMap[$region])){
            return $regionMap[$region];
        }else{
            return $region;
        }
    }



    /**
     * 导出DHL模板
     * @return array
     */
    public function actionExportAramexTpl(){
        $posts = Yii::$app->request->post();
        if(!empty($posts['ids'])){
            $ids = explode(",",$posts['ids']);
            $orderModel = new Order();
            $orders = $orderModel->find()->with('items')->with('address')->where(['in','id',$ids])->andWhere(['shipping_method'=>'ARAMEX'])->all();
            $validOrders = [];
            foreach($orders as $order){
                if($this->canExportDhl($order)){
                    $validOrders[] = $order;
                }
            }
            $header = $this->createExportAramexHeader();
            $data = $this->formatExportAramexData($orders);
            $objExportData = new ExportData($header,$data);
            $objExportData->createExcel($header,$data,20,'快件信息');
            $path = 'download/shipment/template/'.date("Y-m-d").'/'.'orders-'.date('Y-m-d-H-i-s').'-ARAMEX.xls';
            $objExportData->saveFileTo($path);
            return $this->json_output(['data'=>['/'.$path]]);

            return $this->json_output();
        }else{
            return $this->json_output(['status'=>0,'msg'=>'请选择要导出的DHL物流订单']);
        }
    }

    private function createExportAramexHeader(){
        $header = [
            '客户单号(SO NO.)',
            '目的国家二字码',
            '收件人公司名',
            '收件人姓名',
            '城市',
            '邮编',
            '联系地址',
            '收件人电话',
            '包裹描述',
            '包裹总数',
            '总重量',
            '服务方式',
            '报关币种',
            '海关报关品名',
            '商品海关编码(HS CODE)',
            '申报价值',
            '订单备注(PO NO.)',
        ];
        return $header;
    }


    /**
     * 格式化要导出的数据
     * @param $data
     * @return array
     */
    private function formatExportAramexData($orders){
        $output = [];
        foreach($orders as $index=>$order){
            if(!$this->canExport($order))continue;
            $address = $order->address;
            $output[$index][] = $order->ext_order_id;
            $output[$index][] = $address->country_id;
            $output[$index][] = CommonHelper::filterEmptyStr($address->firstname).' '.CommonHelper::filterEmptyStr($address->lastname);
            $output[$index][] = CommonHelper::filterEmptyStr($address->firstname).' '.CommonHelper::filterEmptyStr($address->lastname);
            $output[$index][] = $address->city;
            $output[$index][] = $address->postcode;
            $street = $address->street? str_replace(["\r\n", "\r", "\n"], ' ', $address->street) : '';
            $cityRegion = CommonHelper::filterEmptyStr($address->city).','.CommonHelper::filterEmptyStr($address->region).','.CommonHelper::filterEmptyStr($address->postcode);
            $street .= $cityRegion;
            $output[$index][] = $street;
            $output[$index][] = CommonHelper::filterEmptyStr($address->telephone);
            $output[$index][] = 'ALLOY RING';
            $output[$index][] = '1';
            $output[$index][] = '0.5';
            $output[$index][] = '';
            $output[$index][] = '';
            $output[$index][] = '';
            $output[$index][] = '';
            $output[$index][] = '25';
            $output[$index][] = '';
        }
        return $output;
    }
}