<?php

namespace app\models\product;

use app\models\BaseModel;
use app\models\File;
use app\models\order\Item;
use app\models\order\Order;
use app\models\order\StockOrder;
use renk\yiipal\helpers\ArrayHelper;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\Url;
use yii\web\UploadedFile;

class ProductDelivered extends BaseModel
{

    const DELIVERED_TYPE_CUSTOM = 'custom';
    const DELIVERED_TYPE_STOCK  = 'stock';
    public $ext_order_id = '';
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'product_delivered_history';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = [
            [$this->getTableSchema()->getColumnNames(),'safe'],
            ['ext_order_id','safe'],
        ];
        return $rules;
    }

    /**
     * 获取定制单信息
     * @return \yii\db\ActiveQuery
     */
    public function getCustomOrder(){
        return $this->hasOne(Order::className(), ['id' => 'order_id']);
    }

    /**
     * 获取定制单信息
     * @return \yii\db\ActiveQuery
     */
    public function getCustomOrderItems(){
        return $this->hasOne(Item::className(), ['order_id' => 'order_id']);
    }

    /**
     * 获取库存单信息
     * @return \yii\db\ActiveQuery
     */
    public function getStockOrder(){
        return $this->hasOne(StockOrder::className(), ['id' => 'order_id']);
    }

    /**
     * 获取Item的产品信息
     * @return \yii\db\ActiveQuery
     */
    public function getProduct(){
        return $this->hasOne(Product::className(), ['id' => 'product_id']);
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
        $query = $class::find()->with('customOrder')->with('stockOrder')
        ->leftjoin('order','product_delivered_history.order_id=order.id')
        ->leftjoin('stock_order_item','product_delivered_history.order_id=stock_order_item.id')
        ;
        // add conditions that should always apply here
        $this->load($params);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> ['defaultOrder' => ['created_at'=>SORT_DESC]]
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
            switch($field){
                case 'ext_order_id':
                    if(stripos($item,'S-') !== false){
                        $output[] = ['stock_order_item.'.$field=>ltrim($item,'S-')];
                    }else{
                        $output[] = ['order.'.$field=>$item];
                    }
                    break;
                case 'duration_time':
                    $output[] = ['>=','product_delivered_history.duration_time',$item];
                    break;
                default:
                    $output[] = ['product_delivered_history.'.$field=>$item];
                    break;
            }
        }
        return $output;
    }

    public function getReportData($startTime,$endTime){
        $data = ['costs'=>[]];

        $types = ['stock','custom','all'];

        foreach($types as $type){
            $total = $this->getReportInfo($startTime,$endTime,$type);
            $data['qty_ordered'][] = intval($total['qty_ordered_total']);
            $data['qty_passed'][] = intval($total['qty_passed_total']);
        }
        return $data;
    }

    /**
     * 获取定制生产和库存生产货款
     * @param $startTime
     * @param $endTime
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getReportInfo($startTime,$endTime){
        $customOrderCost = ProductDelivered::find()
            ->innerJoin('product',"product.id=product_delivered_history.product_id")
            ->where(['product_delivered_history.order_type'=>'custom'])
            ->andWhere(['>','product_delivered_history.created_at',$startTime])
            ->andWhere(['<','product_delivered_history.created_at',$endTime])
            ->select(new Expression("sum(product.price*product_delivered_history.qty_passed) as sub_total"))
            ->asArray()->column()
        ;
        $customOrderCost = $customOrderCost[0];

        $stockOrderCost = ProductDelivered::find()
            ->innerJoin('product',"product.id=product_delivered_history.product_id")
            ->where(['product_delivered_history.order_type'=>'stock'])
            ->andWhere(['>','product_delivered_history.created_at',$startTime])
            ->andWhere(['<','product_delivered_history.created_at',$endTime])
            ->select(new Expression("sum(product.price*product_delivered_history.qty_passed) as sub_total"))
            ->asArray()->column()
        ;
        $stockOrderCost = $stockOrderCost[0];

        $totalCost = $customOrderCost+$stockOrderCost;

        $data['total_cost'] = round($totalCost,2);
        $data['custom_cost'] = round($customOrderCost,2);
        $data['stock_cost'] = round($stockOrderCost,2);
        return $data;
    }
}
