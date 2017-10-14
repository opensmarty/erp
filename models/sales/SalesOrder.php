<?php
/**
 * Order.php 
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/8/31
 */

namespace app\models\sales;


use app\models\BaseModel;
use app\models\order\Item;
use app\models\product\Product;
use app\models\searches\User;
use yii\data\ActiveDataProvider;
use yii\db\Expression;

class SalesOrder extends BaseModel{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'order';
    }

    /**
     * 获取订单的统计信息
     * @param $conditions
     * @param string $viewType
     * @param string $analyseType
     * @return array
     */
    public function getOrderList($conditions, $viewType='day',$analyseType ='total'){
        $output = [];
        foreach($conditions as $row){
            $query = self::find()->leftJoin('order_address',"order_address.parent_id=order.id AND order_address.address_type='shipping'");
//            $query->andWhere(['not in','order.status',['pending','cancelled']]);
            foreach($row as $item){
                if(empty($item['value'])) continue;
                if($item['field'] == 'refund_exchange'){
                    if($item['value'] == 1){
                        $status = ['return_completed','return_part_completed','exchange_part_completed'];
                        $query->andWhere(['or',['in','order.status',$status],['>','order.changed',0]]);
                    }else{
                        $status = ['return_completed','return_part_completed','exchange_part_completed'];
                        $query->andWhere(['and',['not in','order.status',$status],['=','order.changed',0]]);
                    }
                    continue;
                }
                if(preg_match('/.*_at$/',$item['field'])){
                    $date = explode("/", $item['value']);
                    if(count($date)==1){
                        $query->andFilterWhere($item);
                    }else{
                        $query->andFilterWhere(['>','order.'.$item['field'],strtotime($date[0].' 00:00:00')]);
                        $query->andFilterWhere(['<','order.'.$item['field'],strtotime($date[1]." 00:00:00")]);
                    }

                }else{
                    $query->andWhere([$item['field']=>$item['value']]);
                }
            }
            if($analyseType == 'total'){
                $total = $query->distinct()->count();
                $output[] = $total;
            }else{
                $format = '%m月%d日';
                if($viewType == 'week'){
                    $format = '%u周';
                }elseif($viewType == 'month'){
                    $format = '%y年%m月';
                }elseif($viewType == 'year'){
                    $format = '%Y年';
                }
                $results =$query->groupBy(new Expression("FROM_UNIXTIME(order.created_at,'".$format."')"))->asArray()->indexBy('unit')->select(new Expression("FROM_UNIXTIME(order.created_at,'".$format."') as unit,count(distinct order.id) as total"))->all();
                $formatResults = [];
                foreach($results as $index => $row){
                    $formatResults[$index] = $row['total'];
                }
                $output[] = $formatResults;
            }
        }

        return $output;
    }

    /**
     * 获取产品的销售统计信息
     */
    public function getSalesList($conditions, $viewType='day',$analyseType ='total'){
        $output = [];
        $productAttrParams = ['stone_type','stone_color','electroplating_color'];
        $productParams = ['sku'];
        foreach($conditions as $row){
            $query = Item::find()->leftJoin('order',"order.id=order_item.order_id")->leftJoin('product',"product.id=order_item.product_id")->leftJoin('product_attributes','product_attributes.product_id=product.id');
            $query->andWhere(['not in','order.status',['pending','cancelled']]);
            $query->andWhere(['not in','order_item.item_status',['pending','cancelled']]);
            foreach($row as $item){
                if(empty($item['value'])) continue;

                if($item['field'] == 'cost_price_start'){
                    $query->andFilterWhere(['>=','product.price',$item['value']]);
                    continue;
                }

                if($item['field'] == 'cost_price_end'){
                    $query->andFilterWhere(['<=','product.price',$item['value']]);
                    continue;
                }

                if($item['field'] == 'sales_price_start'){
                    $query->andFilterWhere(['>=','product.price',$item['value']]);
                    continue;
                }

                if($item['field'] == 'sales_price_end'){
                    $query->andFilterWhere(['<=','product.price',$item['value']]);
                    continue;
                }

                if($item['field'] == 'chosen_uid'){
                    $uid = 0;
                    $user = User::find()->where(['nick_name'=>$item['value']])->one();
                    $uid = $user?$user->id:0;
                    $query->andFilterWhere(["product.chosen_uid"=>$uid]);
                    continue;
                }

                if($item['field'] == 'source'){
                    $query->andFilterWhere(["product.source"=>$item['value']]);
                    continue;
                }

                if($item['field'] == 'website'){
                    $query->andFilterWhere(["order.source"=>$item['value']]);
                    continue;
                }

                if($item['field'] == 'magento_cid'){
                    $tags = trim($item['value'],",");
                    $tags = explode(",",$tags);
                    $sql = '( 0 OR ';
                    foreach($tags as $tag){
                        $sql .= 'find_in_set('.$tag.',product.magento_cid) OR ';
                    }
                    $sql .= ' 0 )';
                    $query->andWhere(new Expression($sql));
                    continue;
                }

                if(preg_match('/.*_at$/',$item['field'])){
                    $date = explode("/", $item['value']);
                    if(count($date)==1){
                        $query->andFilterWhere($item);
                    }else{
                        $query->andFilterWhere(['>','order.'.$item['field'],strtotime($date[0].' 00:00:00')]);
                        $query->andFilterWhere(['<','order.'.$item['field'],strtotime($date[1]." 00:00:00")]);
                    }
                    continue;
                }

                if(in_array($item['field'],$productAttrParams)){
                    $query->andWhere(["product_attributes.".$item['field']=>$item['value']]);
                }elseif(in_array($item['field'],$productParams)){
                    $query->andWhere(["product.".$item['field']=>$item['value']]);
                }else{
                    $query->andWhere(["order.".$item['field']=>$item['value']]);
                }
            }
            if($analyseType == 'total'){
                $total = $query->sum('qty_ordered');
                $query->addSelect('order.grand_total');
                $turnover = $query->distinct()->sum('grand_total');
                $output['total'][] = intval($total);
                $output['turnover'][] = $turnover?round($turnover,0):0;
            }else{
                $format = '%m月%d日';
                if($viewType == 'week'){
                    $format = '%u周';
                }elseif($viewType == 'month'){
                    $format = '%y年%m月';
                }elseif($viewType == 'year'){
                    $format = '%Y年';
                }
                $query->groupBy(new Expression("FROM_UNIXTIME(order.created_at,'".$format."')"))
                      ->asArray()->indexBy('unit');

                $formatResults = [];
                $salesNumberResults = $query->select(new Expression("FROM_UNIXTIME(order.created_at,'".$format."') as unit,sum(qty_ordered) as total"))
                                ->all();
                foreach($salesNumberResults as $index => $row){
                    $formatResults[$index] = $row['total'];
                }

//                $turnoverResults = $query->select(new Expression("FROM_UNIXTIME(order.created_at,'".$format."') as unit,sum(grand_total) as grand_total"))
//                    ->all();
//                foreach($turnoverResults as $index => $row){
//                    $formatResults['grand_total'][$index] = $row['grand_total'];
//                }

                $output[] = $formatResults;
            }
        }

        return $output;
    }

    public function getTop100($conditions=[]){
        $query = Item::find()->with('product')
                    ->select(new Expression("product_id,sku,SUM(qty_ordered) AS qty_ordered"))
                    ->where(["<>","item_status","cancelled"])->groupBy("product_id")
                    ->orderBy("qty_ordered DESC")
                    ;

        foreach($conditions as $name => $value){
            if(preg_match('/.*_at$/',$name)){
                $date = explode("/", $value);
                if(count($date)==1){
                    $query->andFilterWhere([$name=>$value]);
                }else{
                    $query->andFilterWhere(['>',$name,strtotime($date[0].' 00:00:00')]);
                    $query->andFilterWhere(['<',$name,strtotime($date[1]." 23:59:59")]);
                }
            }
        }

        $top100 = $query->limit(100)->all();
        return $top100;
    }

    public function getRank($params){

        $query = Item::find()->with('product')
                ->leftJoin('order','order.id=order_item.order_id')
                ->leftJoin('product','product.id=order_item.product_id')
                ->addSelect(new Expression("order_item.id,order_item.product_id,order_item.sku,SUM(order_item.qty_ordered) AS qty_ordered"))
                ->where(["<>","order_item.item_status","cancelled"])->andWhere(['order.payment_status'=>'processing'])->groupBy("order_item.product_id")
                ->orderBy("qty_ordered DESC")
                ;
        // add conditions that should always apply here
        $this->load($params);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if(empty($params)){
            return $dataProvider;
        }
        $modelName = 'Item';
        if(!isset($params[$modelName]) || !is_array($params[$modelName])){
            $params = [];
        }else{
            $params = $this->formatSearchParams($params[$modelName]);
        }
        foreach($params as $item){
            $key = key($item);
            if($key == 'product_type'){
                switch($item[$key]){
                    case 'rings':
                        $query->andWhere(['product.cid'=>3]);
                        break;
                    case 'ring_single':
                        $query->andWhere(['product.cid'=>3,'is_couple'=>0]);
                        break;
                    case 'ring_couple':
                        $query->andWhere(['product.cid'=>3,'is_couple'=>1]);
                        break;
                    case 'ring_set':
                        $query->andWhere(['product.cid'=>3,'is_couple'=>2]);
                        break;
                    case 'necklace':
                        $query->andWhere(['product.cid'=>4]);
                        break;
                    case 'bracelet':
                        $query->andWhere(['product.cid'=>5]);
                        break;
                    case 'earrings':
                        $query->andWhere(['product.cid'=>25]);
                        break;
                }
            }elseif($key == 'product_created_at'){
                $date = explode("/", $item[$key]);
                if(count($date)==1){
                    $query->andFilterWhere($item);
                }else{
                    $query->andFilterWhere(['>','product.created_at',strtotime($date[0].' 00:00:00')]);
                    $query->andFilterWhere(['<','product.created_at',strtotime($date[1]." 23:59:59")]);
                }
            }elseif(preg_match('/.*_at$/',$key)){
                $date = explode("/", $item[$key]);
                if(count($date)==1){
                    $query->andFilterWhere($item);
                }else{
                    $query->andFilterWhere(['>','order_item.'.$key,strtotime($date[0].' 00:00:00')]);
                    $query->andFilterWhere(['<','order_item.'.$key,strtotime($date[1]." 23:59:59")]);
                }
            }elseif($key == 'website'){
                if($item[$key]){
                    $query->andWhere(['order.source'=>$item[$key]]);
                }
            }else{
                $query->andWhere($item);
            }
        }

        return $dataProvider;
    }
}