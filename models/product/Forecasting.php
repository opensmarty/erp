<?php
/**
 * Forecasting.php 
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/7/4
 */

namespace app\models\product;


use app\helpers\CommonHelper;
use app\helpers\Options;
use app\models\BaseModel;
use app\models\order\Item;
use app\models\Variable;
use renk\yiipal\helpers\Url;
use yii\data\ActiveDataProvider;
use yii\data\Pagination;
use yii\data\SqlDataProvider;

class Forecasting extends BaseModel{
    public $stocksInfo = [];
    public $stocksup = 0;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = [
            [$this->getTableSchema()->getColumnNames(),'safe'],
            [['stocksup'],'safe']
        ];
        return $rules;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'forecasting_log';
    }

    /**
     * 获取产品信息
     * @return \yii\db\ActiveQuery
     */
    public function getProduct(){
        return $this->hasOne(Product::className(), ['id' => 'product_id']);
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
    private function getItemButtons()
    {
        $buttons = [];
        $buttons[] = [
            'label' => '销售历史',
            'url' => Url::toRoute(['sales-history', 'id' => $this->id]),
            'icon' => 'glyphicon glyphicon-wrench',
            'attr' => ['class' => 'btn-product-sales-view'],
        ];
        $buttons[] = [
            'label' => '补库存',
            'url' => Url::toRoute(['add-stocks', 'id' => $this->id]),
            'icon' => 'glyphicon glyphicon-wrench',
            'attr' => ['class' => 'btn-forecasting-add-stocks ajax-modal'],
        ];
        return $buttons;
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
        $query = $class::find()
            ->select('forecasting_log.id,forecasting_log.product_id,forecasting_log.sku,forecasting_log.size,
                        forecasting_log.size_type,forecasting_log.actual_qty,forecasting_log.forecast_qty,forecasting_log.manual_qty,
                        forecasting_log.date_start,forecasting_log.date_end,
                        stock.total AS actual_total,(stock_order_item.qty_ordered-stock_order_item.qty_passed) AS virtual_total')
//            ->addSelect(new \yii\db\Expression('(IFNULL(stock.total,0)+(IFNULL(stock_order_item.qty_ordered,0)-IFNULL(stock_order_item.qty_passed,0))) AS real_total,(IFNULL(forecasting_log.forecast_qty,0)-(IFNULL(stock.total,0)+(IFNULL(stock_order_item.qty_ordered,0)-IFNULL(stock_order_item.qty_passed,0)))) AS stocksup'))
            ->addSelect(new \yii\db\Expression('forecast_qty-(IFNULL(stock.total,0)+IFNULL((stock_order_item.qty_ordered-stock_order_item.qty_passed),0)) AS stocksup'))
            ->leftJoin('stock','stock.product_id = forecasting_log.product_id AND forecasting_log.size_type = stock.type AND stock.size_code = forecasting_log.size')
            ->leftJoin('stock_order_item','stock_order_item.product_id=stock.product_id AND stock_order_item.product_type=stock.type AND stock.size_code=stock_order_item.size_us');
        // add conditions that should always apply here
        $this->load($params);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
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
            $forecastingParams = ['sku','size','date_end'];
            switch($field){
                case in_array($field, $forecastingParams):
                    $field = 'forecasting_log.'.$field;
                    break;
            }
            $output[] = [$field=>$item];
        }
        return $output;
    }

    /**
     * 开始预测
     * @param $forecastDate
     */
    public function startForecasting($forecastDate){
        $forecastingBasedDate = Variable::get('forecasting_based_date',30);
        $startDate = date('Y-m-d',strtotime($forecastDate.' -'.intval($forecastingBasedDate).' days'));
        $endDate = $forecastDate;
        $products = Product::find()->all();
        $sizes = Size::find()->select('size')->asArray()->all();
        foreach($products as $product){
            $sku = $product->sku;
            foreach($sizes as $size){
                //对戒分别预测男女款
                if($product->is_couple == 1){
                    $sizeTypes = ['men','women'];
                    foreach($sizeTypes as $sizeType){
                        $forecastNumber = $this->forecastSalesByDates($startDate, $endDate,$sku, $size['size'],$sizeType);
                        echo "Forecast Number:$forecastNumber--SKU:$sku--Size:{$size['size']}--Start date:$startDate--End date:$endDate\r\n";
                        $this->saveForecasting($forecastNumber,$product,$size['size'],$sizeType,$startDate,$endDate);
                    }
                }else{
                    $forecastNumber = $this->forecastSalesByDates($startDate, $endDate,$sku, $size['size']);
                    echo "Forecast Number:$forecastNumber--SKU:$sku--Size:{$size['size']}--Start date:$startDate--End date:$endDate\r\n";
                    $this->saveForecasting($forecastNumber,$product,$size['size'],'none',$startDate,$endDate);
                }
            }
        }
    }

    /**
     * 保存预测记录
     * @param $forecastNumber
     * @param $product
     * @param $size
     * @param string $sizeType
     * @param $startDate
     * @param $endDate
     */
    private function saveForecasting($forecastNumber,$product,$size,$sizeType='none',$startDate,$endDate){
        //最低库存量
        $minNumber = Variable::get('forecasting_min_number',0);
        $forecastNumber = $forecastNumber<$minNumber?$minNumber:$forecastNumber;
        $coefficient = Variable::get('forecasting_coefficient',1);
        $forecastNumber = round($forecastNumber*$coefficient);
        //预测数没有销量的，不做记录.
        if($forecastNumber<=0) return false;
        echo "Final forecast number ".$forecastNumber."\r\n";
        $model = self::find()->where(['sku'=>$product->sku,'size'=>$size,'size_type'=>$sizeType,'date_start'=>$startDate])->one();
        if(empty($model)){
            $model = new Forecasting();
            $model->product_id = $product->id;
            $model->sku = $product->sku;
            $model->size = $size;
            $model->size_type = $sizeType;
            $model->manual_qty = 0;
            $model->date_start = $startDate;
            $model->date_end = $endDate;
        }
        $model->forecast_qty = $forecastNumber;
        $model->save();
    }

    /**
     * 库存预测
     * @param $startDate
     * @param $endDate
     * @param $sku
     * @param $size
     * @param string $sizeType
     * @return array|bool|float|int|null
     */
    private function forecastSalesByDates($startDate, $endDate, $sku, $size,$sizeType='none'){
        $datePeriod = CommonHelper::getDatePeriod($startDate,$endDate);
        $sql = "SELECT SUM(i.qty_ordered) as qty,FROM_UNIXTIME(i.created_at, '%Y-%m-%d') AS short_order_date FROM `order_item` i "
        ." JOIN `order` o ON i.order_id=o.id"
        ." WHERE o.payment_status='processing'  AND o.status<>'canceled' "
        ." AND i.created_at>:start_time AND i.created_at<:end_time "
        ." AND i.size_us=:size_us AND i.size_type=:size_type AND i.sku=:sku "
        ." GROUP BY short_order_date";
        $results = self::findBySql($sql,['start_time'=>strtotime($startDate),'end_time'=>strtotime($endDate),':size_us'=>$size,'size_type'=>$sizeType,':sku'=>$sku])->asArray()->all();
        if(empty($results)){
            echo "no data SKU: $sku\r\n";
            return 0;
        }
        $data = [];
        foreach($results as $row){
            if(empty($row))continue;
            $data[$row['short_order_date']] = $row['qty'];
        }

        $datePeriod = array_fill_keys(array_values($datePeriod), 0);
        $data = array_merge($datePeriod, $data);
        $arraySum = array_sum($data);
        if($arraySum == 0){
            $forecastNumber = 0;
            echo "defalult number $forecastNumber\r\n";
        }
        else{
            $arrayValues = array_values($data);
            if($arraySum>60){
                $newData = $this->getNewData($arrayValues,5);
                echo "method:Average 5 \r\n";
            }elseif($arraySum>30){
                $newData = $this->getNewData($arrayValues,10);
                echo "method:Average 10 \r\n";
            }elseif($arraySum>15){
                $newData = $this->getNewData($arrayValues,15);
                echo "method:Average 15 \r\n";
            }else{
                $newData = $this->getNewData($arrayValues,30);
                echo "method:Average 30 \r\n";
            }
            $forecastNumber = $this->getAverageValue($newData,10);
        }
        return $forecastNumber;
    }

    private function getNewData($data,$number=20){
        $length = count($data);
        if($length>$number){
            return array_slice($data,$length-$number);
        }else{
            return $data;
        }
    }

    /**
     * 平均值法测算法
     * @param $data
     * @param int $forecast_days
     * @return float|int
     */
    private function getAverageValue($data, $forecast_days=10){
        $forecast_number = array_sum($data)/count($data)*$forecast_days;
        return $forecast_number;
    }

    /**
     * 数值趋势测算法
     * @param int $datas 数据样本
     * @param int $k 测算期数
     * @param int $n 平均值长度
     * @return array 预测数据
     * @author leeldy
     */
    private function getTendencyValue($datas, $k = 1, $n = false) {

        //数据期数
        $t = count($datas);
        //判断n是否满足要求
        if ($n) {
            if ($t < $n + $n - 1) {
                exit('平均值长度n数值过大！');
            }
        } else {
            //取最大的n值
            $n = intval(($t + 1) / 2);
        }
        $m = array(
            //一次平均值
            1 => array(),
            //二次平均值
            2 => array()
        );
        //前n项和
        $m_1 = 0;
        //前n项一次平均值和
        $m_2 = 0;
        //一次平均值开始计算点下标
        $n_1 = $n - 1 - 1;
        //二次平均值开始计算点下标
        $n_2 = $n_1 + $n - 1;
        //计算平均值
        for ($i = 0; $i < $t; $i++) {
            //数据前n项和
            $m_1 += $datas[$i];
            if ($i > $n_1) {
                //开始计算一次平均值
                $m[1][$i] = $m_1 / $n;
                //去除最前一项
                $m_1 -= $datas[$i - $n + 1];
                //一次平均值前n项和
                $m_2 += $m[1][$i];
                if ($i > $n_2) {
                    //计算二次平均值
                    $m[2][$i] = $m_2 / $n;
                    $m_2 -= $m[1][$i - $n + 1];
                }
            }
        }
        //计算基本值和趋势系数
        $at = $m[1][$t - 1] + $m[1][$t - 1] - $m[2][$t - 1];
        $bt = 2 / ($n - 1) * ($m[1][$t - 1] - $m[2][$t - 1]);
        //计算趋势
        $result = array($at);
        $i = 0;
        $num = 0;
        while (++$i < $k) {
            $result[$i] = $result[$i - 1] + $bt;
            $num += $result[$i];
        }
        return $num;
    }

    public function actualSalesValid($date){
        $models = true;
        $offset = 0;
        while($models){
            $models = self::find()->where(["date_end"=>$date])->limit(100)->offset($offset*100)->all();
            if(!empty($models)){
                foreach($models as $model){
                    $this->getActualSalesNumber($model);
                }
            }else{
                $models = false;
            }
            $offset++;
        }
    }

    private function getActualSalesNumber($model){
        $qty_ordered = Item::find()->where(['sku'=>$model->sku,'size_us'=>$model->size,'size_type'=>$model->size_type])
                        ->andWhere(['not in','item_status',['pending','cancelled']])
                        ->andWhere(['>','created_at',strtotime($model->date_end)])
                        ->andWhere(['<','created_at',strtotime($model->date_end .' +11 days')])
                        ->sum('qty_ordered');
        $model->actual_qty = intval($qty_ordered);
        echo "SKU:".$model->sku."--Size:".$model->size."--Size Type:".$model->size_type.'--actual sales:'.$model->actual_qty."\r\n";
        $model->save();
    }
}