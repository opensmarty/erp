<?php

namespace app\models\product;

use app\models\BaseModel;
use app\models\File;
use app\models\order\Item;
use app\models\User;
use renk\yiipal\helpers\ArrayHelper;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\Url;
use yii\web\UploadedFile;

class Product extends BaseModel
{

    public $stocksInfo = [];
    public $salesInfo = [];
    public $stock_total =0;
    public $virtual_total ='';
    public $attr_complete = '';
    public $factory_price = '';
    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = [
            [['name','sku'], 'trim'],
            ['sku','unique'],
            [['name','sku','price','type','cid'], 'required'],
            [['id','taobao_url','is_couple','magento_cid','chosen_uid','source','files','template_no','stock_total','virtual_total','attr_complete','factory_price','attr_uid','is_clean'],'safe'],
            [['price'], 'number'],
            ['uid', 'default', 'value' => \Yii::$app->user->id],
            [['files'], 'file', 'skipOnEmpty' => true, 'maxFiles' => 8],
            [['template_no'], 'required', 'on' => 'update_attributes'],
        ];

        return $rules;
    }

    public function attributeLabels()
    {
        return [
            'id' => '产品编号',
            'name' => '产品名',
            'sku' => 'SKU',
            'template_no' => '版号',
            'chosen_uid' => '选款人',
            'source' => '来源',
            'price' => '进货价',
            'cid' => '产品分类',
            'magento_cid' => 'Magento 类目',
            'files' => '产品图片',
            'is_couple' => '对戒',
            'stone_type' => '主钻类型',
            'is_clean' => '是否清仓',
        ];
    }

    /**
     * 列表也下拉按钮
     * @return array
     */
    public function buttons(){

        $buttons[] =             [
            'label'=>'查看',
            'url'=>Url::toRoute(['view','id'=>$this->id]),
            'icon'=>'',
            'attr' =>'',
        ];

        $buttons[] = [
            'label'=>'编辑',
            'url'=>Url::toRoute(['update','id'=>$this->id]),
            'icon'=>'',
            'attr' =>'',
        ];

        $buttons[] = [
            'label'=>'删除',
            'url'=>Url::toRoute(['delete','id'=>$this->id]),
            'icon'=>'',
            'attr' =>['class'=>'confirm'],
        ];

        $buttons[] = [
            'label'=>'补库存',
            'url'=>Url::toRoute(['/product/product/add-stocks','id'=>$this->id]),
            'icon'=>'glyphicon glyphicon-wrench',
            'attr' =>['class'=>'btn-order-expedite ajax-modal'],
        ];

        $buttons[] = [
            'label'=>'编辑库存',
            'url'=>Url::toRoute(['/product/product/edit-stocks','id'=>$this->id]),
            'icon'=>'glyphicon glyphicon-wrench',
            'attr' =>['class'=>'btn-order-expedite ajax-modal'],
        ];
        return $buttons;
    }

    /**
     * 类型转货
     * @param $type
     * @return string
     */
    static public function getTypeLabel($type){
        $label = '';
        switch($type){
            case 'taobao':
                $label = '淘宝款';
                break;
            case 'factory':
                $label = '工厂款';
                break;
            case 'virtual':
                $label = '虚拟产品';
                break;
        }
        return $label;
    }

    /**
     * 获取类型选项
     * @return array
     */
    public function getTypeOptions(){
        $options = [];
        $options['taobao'] = '淘宝款';
        $options['factory'] = '工厂款';
        $options['virtual'] = '虚拟产品';
        return $options;
    }

    /**
     * 获取要导出的数据
     * @param $ids
     * @return array
     */
    public function getExportData($ids){
        $query = new Query();
        $query->select('p.id,p.sku,p.template_no,p.type,p.is_couple,f.file_path')
            ->from('product p')
            ->leftJoin('file_managed f','f.id=p.fids')
            ->where(['in','p.id',$ids])
            ;
        return $query->all();
    }

    /**
     * 获取要导出的数据
     * @param $ids
     * @return array
     */
    public function getExportDataProducts($ids){
        $query = Product::find()->with('recordUser');
        $query->where(['in','id',$ids]);
        return $query->all();
    }
    
    /**
     * 获取要导出的数据
     * @param $ids
     * @return array
     */
    public function getExportProductsBySku($skus){
        $query = new Query();
        $query->select('p.sku,f.file_path')
        ->from('product p')
        ->leftJoin('file_managed f','f.id=p.fids')
        ->where(['in','p.sku',$skus])
        ;
        return $query->all();
    }

    /**
     * 获取产品的库存信息
     * @return \yii\db\ActiveQuery
     */
    public function getStocks(){
        return $this->hasMany(Stock::className(), ['product_id' => 'id']);
    }

    /**
     * 获取产品的录入人信息
     * @return \yii\db\ActiveQuery
     */
    public function getRecordUser(){
        return $this->hasOne(User::className(), ['id' => 'uid']);
    }

    /**
     * 获取编辑产品属性的人的信息
     * @return \yii\db\ActiveQuery
     */
    public function getAttrUser(){
        return $this->hasOne(User::className(), ['id' => 'attr_uid']);
    }

    /**
     * 获取产品的选款人人信息
     * @return \yii\db\ActiveQuery
     */
    public function getChosenUser(){
        return $this->hasOne(User::className(), ['id' => 'chosen_uid']);
    }

    /**
     * 获取产品图片
     * @return \yii\db\ActiveQuery
     */
//    public function getImage(){
//        return $this->hasOne(File::className(), [new Expression('id in (fids)')]);
//    }

    /**
     * 获取产品主图
     * @return bool|null|static
     */
    public function getMasterImage(){
        $fids = explode(",",$this->fids);
        if($fids){
            return File::getFile($fids[0]);
        }else{
            return false;
        }
    }

    /**
     * 获取产品属性
     * @return \yii\db\ActiveQuery
     */
    public function getProductAttributes(){
        return $this->hasOne(ProductAttributes::className(),['product_id'=>'id']);
    }

    /**
     * Creates data provider instance with search query applied
     * @param $class
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params=null, $class=null,$defaultParams=false)
    {
        if($class == null){
            $class = self::className();
        }
        $query = $class::find()->with('chosenUser')->with('recordUser');
        $query->addSelect(new Expression('*,IFNULL((SELECT SUM(qty_ordered) - SUM(qty_passed) FROM  stock_order_item WHERE stock_order_item.product_id = product.id AND item_status <> "purchase_completed" AND item_status <> "product_passed" ),0) AS virtual_total, IFNULL((SELECT SUM(total) FROM stock WHERE stock.product_id=product.id ),0) AS actual_total'));
        // add conditions that should always apply here
        $this->load($params);
        if(!isset($params['sort'])){
            $query->orderby("product.created_at DESC");
        }
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
//            'sort'=> ['defaultOrder' => ['created_at'=>SORT_DESC]]
        ]);

        $this->formatQueryParams($query, $params,$defaultParams);

        return $dataProvider;
    }

    /**
     * 格式化查询参数
     * @param $query
     * @param null $params
     */
    protected function formatQueryParams(&$query, $params=[],$defaultParams=false){

        if(empty($params) && $defaultParams==false){
            return false;
        }
        $modelName = basename(str_replace('\\','/',self::className()));
        if(!isset($params[$modelName]) || !is_array($params[$modelName])){
            $params = [];
        }else{
            $params = $this->formatSearchParams($params[$modelName]);
        }
        foreach($params as $item){
            $key = key($item);
            if($key === 'stock_total'){
                if(empty($item[$key])){
                    continue;
                }

                switch($item[$key]){
                    case -2:
                        $query->andHaving(new Expression('(actual_total+virtual_total)<=0'));
                        break;
                    case -1:
                        $query->andHaving(new Expression('(actual_total+virtual_total)>0'));
                        break;
                    case '1-10':
                        $query->andHaving(new Expression('(actual_total+virtual_total)>=1 AND (actual_total+virtual_total)<10'));
                        break;
                    case '10-50':
                        $query->andHaving(new Expression('(actual_total+virtual_total)>=10 AND (actual_total+virtual_total)<50'));
                        break;
                    case '50-100':
                        $query->andHaving(new Expression('(actual_total+virtual_total)>=50 AND (actual_total+virtual_total)<100'));
                        break;
                    case '100-1000':
                        $query->andHaving(new Expression('(actual_total+virtual_total)>=100 AND (actual_total+virtual_total)<1000'));
                        break;
                    case '1000-10000':
                        $query->andHaving(new Expression('(actual_total+virtual_total)>=1000 AND (actual_total+virtual_total)<10000'));
                        break;
                }
                continue;
            }
            if($key === 'virtual_total'){
                if($item[$key]>0){
                    $query->andHaving(new Expression('(virtual_total)>0'));
                }else{
                    $query->andHaving(new Expression('(virtual_total)<=0'));
                }
                continue;
            }
            if($key === 'attr_uid'){
                $uid = 0;
                $user = User::find()->where(['nick_name'=>$item[$key]])->one();
                $uid = $user?$user->id:0;
                $query->andFilterWhere([$key=>$uid]);
                continue;
            }
            if(preg_match('/.*_at$/',$key)){
                $date = explode("/", $item[$key]);
                if(count($date)==1){
                    $query->andFilterWhere($item);
                }else{
                    $query->andFilterWhere(['>','product.'.$key,strtotime($date[0].' 00:00:00')]);
                    $query->andFilterWhere(['<','product.'.$key,strtotime($date[1]." 23:59:59")]);
                }

            }elseif($key === 'factory_price'){
                if($item[$key] == '1'){
                    $query->andFilterWhere(['>','price',0]);
                }elseif($item[$key] == '-1'){
                    $query->andFilterWhere(['<','price',1]);
                }

            }elseif($key === 'attr_complete'){
                $operator = ' is not null ';
                $join = ' AND ';
                if($item[$key] == '0'){
                    $operator = ' is null ';
                    $join = ' OR ';
                }
                $query->leftjoin('product_attributes','product_attributes.product_id=product.id')->select("product.*");
                $firstRingQuery = ' product_attributes.stone_size '.$operator.$join.' product_attributes.stone_carat'.$operator.$join.' product_attributes.side_stone_number'.$operator.$join.' product_attributes.width'.$operator.$join.' product_attributes.thickness'.$operator.$join.' product_attributes.weight'.$operator.$join.' product_attributes.side_stone_size'.$operator.' ';
                $secondRingQuery = ' product_attributes.stone_2_size '.$operator.$join.' product_attributes.stone_2_carat'.$operator.$join.' product_attributes.side_stone_2_number'.$operator.$join.' product_attributes.width_2'.$operator.$join.' product_attributes.thickness_2'.$operator.$join.' product_attributes.weight_2'.$operator.$join.' product_attributes.side_stone_2_size'.$operator.' ';
                $singleRingQuery = '(product.cid=3 AND ('.$firstRingQuery.'))';
                $multiRingQuery = '(product.cid=3 AND (product.is_couple=1 OR product.is_couple=2) AND ('.$firstRingQuery.') AND ('.$secondRingQuery.'))';
                $necklaceQuery = '(product.cid=4 AND ( product_attributes.necklace_stone_number'.$operator.$join.' product_attributes.necklace_length'.$operator.$join.' product_attributes.necklace_pendant_height'.$operator.$join.' product_attributes.necklace_pendant_width'.$operator.$join.' product_attributes.necklace_stone_size'.$operator.'))';
                $query->andWhere(' ('.$singleRingQuery.' OR '.$multiRingQuery.' OR '.$necklaceQuery.')');
            }

            else{
                $query->andFilterWhere($item);
            }

        }
        if($defaultParams){
            foreach($defaultParams as $defaultParam){
                $query->andFilterWhere($defaultParam);
            }
        }
    }

    /**
     * 处理查询参数.
     * @param $params
     * @return array
     */
    public function formatSearchParams($params){
        $productTypes = [
            'rings'=>'3',
            'necklace'=>'4',
            'bracelet'=>'5',
            'earrings'=>'25',
        ];
        $output = [];
        foreach($params as $field=>$item){
            $item = trim($item);
            if($item==''){
                continue;
            }
            $likeParams = ['name','sku','template_no'];
            switch($field){
                case in_array($field, $likeParams):
                    $output[] = ['like',$field,$item];
                    break;
                case 'cid':
                    if(isset($productTypes[$item])){
                        $output[] = [$field=>$productTypes[$item]];
                    }else if($item == 'ring_couple'){
                        $output[] = ['is_couple'=>1];
                        $output[] = ['cid'=>3];
                    }else if($item == 'ring_set'){
                        $output[] = ['is_couple'=>2];
                        $output[] = ['cid'=>3];
                    }else{
                        $output[] = ['is_couple'=>0];
                        $output[] = ['cid'=>3];
                    }
                    $output[] = ['<>','type','virtual'];
                    break;
                default:
                    $output[] = [$field=>$item];
            }
        }
        return $output;
    }

    /**
     * 获取产品的销售历史
     * @param $productId
     * @param $size
     * @param $sizeType
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getSalesHistory($productId,$size,$sizeType,$dateStart,$dateEnd){
        $sql = "SELECT SUM(qty_ordered) AS qty, FROM_UNIXTIME(order_item.created_at, '%Y-%m-%d') AS short_date FROM order_item"
            ." INNER JOIN `order` ON order.id=order_item.order_id WHERE order_item.product_id=:product_id AND "
            ." order.payment_status='processing' AND order.status <> 'cancelled' AND order.source <> 'SYS' "
            ." AND order.created_at>:date_start AND order.created_at<:date_end"
            ." AND order_item.size_us=:size AND order_item.size_type=:size_type GROUP BY short_date ";
        return self::findBySql($sql,[':product_id'=>$productId,':size'=>$size,':size_type'=>$sizeType,':date_start'=>strtotime($dateStart),':date_end'=>strtotime($dateEnd)])
            ->asArray()->all();
    }

    /**
     * 产品销售历史
     * @param $productId
     * @param $dateTimeStart
     * @param $dateTimeEnd
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getProductSalesHistory($productId,$dateTimeStart,$dateTimeEnd){
        $sql = "SELECT SUM(qty_ordered) AS qty, FROM_UNIXTIME(order.created_at, '%Y-%m-%d') AS short_date FROM order_item"
            ." INNER JOIN `order` ON order.id=order_item.order_id WHERE order_item.product_id=:product_id AND "
            ." order.payment_status='processing' AND order.status <> 'cancelled' AND order.source <> 'SYS' "
            ." AND order.created_at>:date_start AND order.created_at<:date_end"
            ." AND order_item.item_status not in('cancelled','return_process','return_completed') GROUP BY short_date ORDER BY short_date ASC ";
        return self::findBySql($sql,[':product_id'=>$productId,':date_start'=>$dateTimeStart,':date_end'=>$dateTimeEnd])
            ->asArray()->all();
    }

    /**
     * 保存
     * @param bool $runValidation
     * @param null $attributeNames
     */
    public function save($runValidation = true, $attributeNames = null){
        if($this->files && UploadedFile::getInstances($this,'files')){
            $fileModel = new File();
            $path = 'products/'.$this->sku;
            $fileIds = $fileModel->upload($this,'files',$path);
            if(empty($this->fids)){
                $this->fids = $fileIds?join(",", $fileIds):'';
            }else{
                $fids = $fileIds?join(",", $fileIds):'';
                if(!empty($fids)){
                    $this->fids .= ','.$fids;
                }
            }

        }
        $this->files = null;
        return parent::save();
    }

    /**
     * 获取产品的库存数据
     * @return array
     */
    public function getProductStocks(){
//        $query = new Query();
//        $query->select("s.product_id,s.type,s.size_id,s.size_code, s.total as actual_total,(so.qty_ordered-so.qty_passed) AS virtual_total")
//            ->from("stock s")
//            ->leftJoin("`stock_order_item` as so ","`s`.`product_id`=so.`product_id` AND s.`type`=so.`product_type` AND s.`size_code` = so.`size_us` AND so.`item_status`<>:purchase_completed AND so.`item_status`<>:product_passed",[':purchase_completed'=>'purchase_completed',':product_passed'=>'product_passed'])
//            ->where(["s.product_id"=>$this->id])
//            ->orderBy("s.type DESC,s.size_id ASC")
//        ;
//        $results = $query->all();
        $results = self::findBySql('
                        SELECT
                          s.product_id,
                          s.type,
                          s.size_id,
                          s.size_code,
                          s.total AS actual_total,
                          (SELECT SUM(qty_ordered)-SUM(qty_passed) FROM stock_order_item WHERE product_id=s.product_id AND product_type=s.type AND size_us=s.size_code AND item_status NOT IN ("purchase_completed","product_passed") ) AS virtual_total
                        FROM
                          stock s
                        WHERE
                        s.product_id = :product_ids
                        ORDER BY
                        s.type DESC,
                        s.size_id ASC
                  ',[':product_ids'=>$this->id])->asArray()->all();

        if(empty($results)){
            return [];
        }
        $output = [];
        foreach($results as $row){
            $row['total'] = intval($row['actual_total']) + intval($row['virtual_total']);
            $output[$row['type']][$row['size_id']] = $row;
        }
        return $output;
    }

    /**
     * 获取尺码对应的库存数
     * @param int $size
     * @param string $type
     * @return int
     */
    public function getProductStocksNumber($size=0,$type='none'){
        $data = $this->getProductStocks();
        if(empty($data)){
            return 0;
        }
        if(isset($data[$type]) && isset($data[$type][$size])){
            return $data[$type][$size]['actual_total'];
        }else{
            return 0;
        }
    }

    /**
     * 获取指定ID的库存数据
     * @param array $productIds
     * @return array
     */
    public function getProductsStocks($productIds=[]){

        $virtualStocks = $this->find()->select(new Expression('product_id,product_type as type,size_us as size_code,0 as actual_total'))->addSelect(new Expression('SUM(qty_ordered)-SUM(qty_passed) as virtual_total'))
                        ->from('stock_order_item')
                        ->where(['not in','item_status',["purchase_completed","product_passed"]])
                        ->andWhere(['in','product_id',$productIds])
//                        ->andHaving(['>','virtual_total',0])
                        ->orderBy('type DESC,size_us ASC')
                        ->groupBy('product_id, product_type,size_us')
                        ->asArray()->all();
        $actualStocks = $this->find()->select(new Expression('product_id,type,size_code,total as actual_total,0 as virtual_total'))->from('stock')->where(['and',['<>','total',0]])
                        ->andWhere(['in','product_id',$productIds])
                        ->orderBy('type DESC,size_id ASC')->asArray()->all();
        $output = [];
        $results = [];
        if(!empty($actualStocks)){
            $results = $actualStocks;
        }

        if(!empty($virtualStocks)){
            if(empty($results)){
                $results = $virtualStocks;
            }else{
                foreach($virtualStocks as $virtualItem){
                    $matched = false;
                    foreach($results as &$actualItem){
                        if($virtualItem['product_id'] == $actualItem['product_id'] && $virtualItem['type'] == $actualItem['type'] && $virtualItem['size_code']==$actualItem['size_code']){
                            $actualItem['virtual_total'] = $virtualItem['virtual_total'];
                            $matched = true;
                            break;
                        }
                    }
                    if(!$matched){
                        $results[] = $virtualItem;
                    }
                }
            }
        }
        if(empty($results)){
            return $results;
        }
        foreach($results as $index=>$row){
            if($row['actual_total'] == 0 && $row['virtual_total'] == 0){
                unset($results[$index]);
            }else{
                $output[$row['product_id']][] = $row;
            }
        }
        return $output;
    }

    /**
     * 获取产品尺码和类型对应的库存数据
     * @param array $productIds
     * @return array
     */
    public function getProductsStocksInfo($productIds=[]){
        //TODO:处理库存预测
        if(empty($productIds)){
            $productIds = 0;
        }else{
            $productIds = join(",",$productIds);
        }

        $results = self::findBySql('
                        SELECT
                          s.product_id,
                          s.type,
                          s.size_code,
                          s.total AS actual_total,
                          (SELECT SUM(qty_ordered)-SUM(qty_passed) FROM stock_order_item WHERE product_id=s.product_id AND product_type=s.type AND size_us=s.size_code AND item_status NOT IN ("purchase_completed","product_passed") ) AS virtual_total
                        FROM
                          stock s
                        WHERE
                        s.product_id IN ('.$productIds.')
                        ORDER BY
                        s.type DESC,
                        s.size_id ASC
                  ')->asArray()->all();
        if(empty($results)){
            return [];
        }
        $output = [];
        foreach($results as $row){
            $output[$row['product_id']][$row['type']][$row['size_code']]['actual_total'] = $row['actual_total'];
            $output[$row['product_id']][$row['type']][$row['size_code']]['virtual_total'] = $row['virtual_total'];
            $output[$row['product_id']][$row['type']][$row['size_code']]['total'] = intval($row['actual_total']+$row['virtual_total']);
        }
        return $output;
    }

    /**
     * 导入产品价格
     * @return bool
     */
    public function importPrice(){
        $files = UploadedFile::getInstances($this, 'files');

        if(empty($files)){
            $this->addError('files', '请上传CSV文件!');
            return false;
        }

        $file = $files[0];

        if (($handle = fopen($file->tempName, "r")) === FALSE) {
            $this->addError('files', '导入失败');
            return false;
        }

        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if( !$data ) {
                continue;
            }

            $product = Product::find()->where(['sku'=>$data[0]])->one();
            if( $product ) {
                $product->price = $data[1];
                $product->save();
            }
        }
        fclose($handle);
        return true;
    }

    /**
     * 导入产品库存
     * @return bool
     */
    public function importStocks($increment = false){
        $files = UploadedFile::getInstances($this, 'files');

        if(empty($files)){
            $this->addError('files', '请上传CSV文件!');
            return false;
        }

        $file = $files[0];

        if (($handle = fopen($file->tempName, "r")) === FALSE) {
            $this->addError('files', '导入失败');
            return false;
        }
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if( !$data ) {
                continue;
            }
            $product = Product::find()->where(['sku'=>$data[0]])->one();
            if( $product ) {
                $stock = Stock::find()->where(['product_id'=>$product->id,'size_id'=>$data[1],'type'=>$data[2]])->one();
                if(empty($stock)){
                    $stock = new Stock();
                    $stock->product_id=$product->id;
                    $stock->uid = $this->getCurrentUid();
                    $stock->size_id = $data[1];
                    if($stock->size_id == 0){
                        $stock->size_code = 0;
                    }else{
                        $stock->size_code = $stock->size_id.'(U.S)';
                    }
                    $stock->type = $data[2];
                }
                if($increment == true){
                    $stock->total += $data[3];
                }else{
                    $stock->total = $data[3];
                }

                $stock->save();
            }
        }
        fclose($handle);
        return true;
    }

    public function getSalesInfo($productIds,$start, $end){
        $output = [];
        $query = Item::find()
                ->select(new Expression("SUM(order_item.qty_ordered) AS qty_ordered,order_item.product_id,order_item.sku,order_item.size_type,order_item.size_us"))
                ->leftJoin('order','order.id=order_item.order_id')
                ->where(['order.payment_status'=>'processing'])
                ->andWhere(['<>','order.status','cancelled'])
                ->andWhere(['<>','order_item.item_status','cancelled'])
                ->andWhere(['>=','order.created_at',$start])
                ->andWhere(['<=','order.created_at',$end])
                ->andWhere(['in','order_item.product_id',$productIds])
                ->groupBy("order_item.product_id, order_item.size_type, order_item.size_type")
                ->orderBy('order_item.size_type DESC,order_item.size_us ASC')
                ;
        $results = $query->asArray()->all();
        if(empty($results)){
            return $output;
        }
        foreach($results as $row){
            $output[$row['product_id']][] = $row;
        }
        return $output;
    }

    /**
     * 获取产品类型
     * @return string
     */
    public function getType(){
        $type = 'ring';
        switch($this->cid){
            case 3:
                $type = 'ring';
                break;
            case 4:
                $type = 'necklace';
                break;
            case 5:
                $type = 'bracelet';
                break;
            case 25:
                $type = 'earring';
                break;
        }
        return $type;
    }
}
