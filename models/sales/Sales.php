<?php
/**
 * Sales.php 
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/8/26
 */

namespace app\models\sales;


use app\models\BaseModel;
use app\models\order\Item;
use app\models\order\Order;
use app\models\product\Product;
use yii\data\ActiveDataProvider;
use yii\db\Expression;

class Sales extends BaseModel{

    public $stock_total = 0;
    public $sales_total = 0;
    public $stocksInfo = [];
    public $salesInfo = [];
    public $sales = '';
    public $template_no = '';


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'order_item';
    }

    public function attributeLabels()
    {
        return [
            'sku' => 'SKU',
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = [
            [$this->getTableSchema()->getColumnNames(),'safe'],
            [['stock_total','sales_total','stocksInfo','template_no'],'safe'],
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
     * 获取Item对应的订单项目.
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(Order::className(), ['id' => 'order_id']);
    }
    public function search($params=null, $class=null,$defaultParams=false)
    {
        if($class == null){
            $class = self::className();
        }
        $query = $class::find()->with('product')->with('order');
        $query->leftjoin('order','order.id=order_item.order_id');
        $query->leftjoin('product','product.id=order_item.product_id');
        $query->addSelect(new Expression('IFNULL((SELECT SUM(qty_ordered) - SUM(qty_passed) FROM  stock_order_item WHERE stock_order_item.product_id = product.id AND item_status <> "purchase_completed" AND item_status <> "product_passed" ),0) AS virtual_total, IFNULL((SELECT SUM(total) FROM stock WHERE stock.product_id=product.id ),0) AS actual_total'));
        $query->addSelect(new Expression("order_item.id,order_item.sku,order_item.product_id, SUM(order_item.`qty_ordered`) AS qty_ordered "));
        $query->where(['order.payment_status'=>'processing']);
        $query->groupBy("order_item.sku");
        // add conditions that should always apply here
        $this->load($params);

//        if(isset($params['sort']) && $params['sort'] == 'qty_ordered'){
//            $query->orderby("product.created_at DESC");
//        }

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

            if($key === 'template_no'){
                $query->andFilterWhere(['product.'.$key=>$item]);
                continue;
            }

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

            if($key === 'qty_ordered'){
                if(empty($item[$key])){
                    continue;
                }
                switch($item[$key]){
                    case -2:
                        $query->andHaving(new Expression('qty_ordered<=0'));
                        break;
                    case -1:
                        $query->andHaving(new Expression('qty_ordered>0'));
                        break;
                    case '1-10':
                        $query->andHaving(new Expression('qty_ordered>=1 AND qty_ordered<10'));
                        break;
                    case '10-50':
                        $query->andHaving(new Expression('qty_ordered>=10 AND qty_ordered<50'));
                        break;
                    case '50-100':
                        $query->andHaving(new Expression('qty_ordered>=50 AND qty_ordered<100'));
                        break;
                    case '100-1000':
                        $query->andHaving(new Expression('qty_ordered>=100 AND qty_ordered<1000'));
                        break;
                    case '1000-10000':
                        $query->andHaving(new Expression('qty_ordered>=1000 AND qty_ordered<10000'));
                        break;
                }
                continue;
            }

            if(preg_match('/.*_at$/',$key)){
                $date = explode("/", $item[$key]);
                if(count($date)==1){
                    $query->andFilterWhere($item);
                }else{
                    $query->andFilterWhere(['>','order_item.'.$key,strtotime($date[0].' 00:00:00')]);
                    $query->andFilterWhere(['<','order_item.'.$key,strtotime($date[1]." 23:59:59")]);
                }
            }

            else{
                $query->andFilterWhere(['order_item.'.$key=>$item]);
            }

        }
        if($defaultParams){
            foreach($defaultParams as $defaultParam){
                $query->andFilterWhere($defaultParam);
            }
        }
    }


    public function getSalesInfo($productIds,$start=false, $end=false){
        $output = [];
        $query = Item::find()
            ->leftJoin("order","order.id=order_item.order_id")
            ->select(new Expression("SUM(order_item.qty_ordered) AS qty_ordered,order_item.product_id,order_item.sku,order_item.size_type,order_item.size_us"))
            ->groupBy("order_item.product_id, order_item.size_type, order_item.size_us")
            ->orderBy('order_item.size_type DESC,order_item.size_us ASC')
        ;
        $query->where(['order.payment_status'=>'processing']);
        if($start && $end){
            $query->andWhere(['>=','order_item.created_at',$start])
                  ->andWhere(['<=','order_item.created_at',$end]);
        }
        $query->andWhere(['in','order_item.product_id',$productIds]);
        $query->andWhere(['<>','order_item.item_status','cancelled']);
        $results = $query->asArray()->all();
        if(empty($results)){
            return $output;
        }
        foreach($results as $row){
            $output[$row['product_id']][] = $row;
        }
        return $output;
    }
}