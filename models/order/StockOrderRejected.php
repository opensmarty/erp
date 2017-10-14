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
use mdm\admin\models\searchs\User;
use renk\yiipal\helpers\Url;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

class StockOrderRejected extends BaseModel{
    public $categories = [];
    const STATUS_REJECTED        = 'rejected';
    const STATUS_SOLVED        = 'solved';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'stock_order_rejected';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = [
            [$this->getTableSchema()->getColumnNames(),'safe'],
        ];
        return $rules;
    }


    /**
     * 获取产品的报告人信息
     * @return \yii\db\ActiveQuery
     */
    public function getReportUser(){
        return $this->hasOne(User::className(), ['id' => 'report_uid']);
    }

    /**
     * 获取产品的报告人信息
     * @return \yii\db\ActiveQuery
     */
    public function getSolvedUser(){
        return $this->hasOne(User::className(), ['id' => 'solved_uid']);
    }

    /**
     * 获取产品信息
     * @return \yii\db\ActiveQuery
     */
    public function getProduct(){
        return $this->hasOne(Product::className(), ['id' => 'product_id']);
    }

    /**
     * 获取定制单信息
     * @return \yii\db\ActiveQuery
     */
    public function getCustomOrder(){
        return $this->hasOne(Order::className(), ['id' => 'order_id']);
    }

    /**
     * 获取库存单信息
     * @return \yii\db\ActiveQuery
     */
    public function getStockOrder(){
        return $this->hasOne(StockOrder::className(), ['id' => 'order_id']);
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
        if($this->item_status == 'rejected'){
            $buttons[] = [
                'label' => '解决',
                'url' => Url::toRoute(['/distribution/rejects/solved?id=' . $this->id]),
                'icon' => 'glyphicon glyphicon-wrench',
                'attr' =>['class'=>'btn-request-accept ajax-modal'],
            ];
        }
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
        $query = $class::find()->with('customOrder')->with('stockOrder')
            ->leftjoin('order','stock_order_rejected.order_id=order.id')
            ->leftjoin('stock_order_item','stock_order_rejected.order_id=stock_order_item.id')
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
                case 'report_uid':
                case 'solved_uid':
                    $user = User::find()->where(['nick_name'=>$item])->one();
                    if($user){
                        $uid = $user->id;
                    }else{
                        $uid = -1;
                    }
                    $output[] = ['stock_order_rejected.'.$field=>$uid];
                    break;
                case 'reject_tags':
                    $tags = explode(",",$item);
                    $express = ' (';
                    foreach($tags as $index =>$tag){
                        if(empty($tag)) continue;
                        $express .= $index>0?' OR ':'';
                        $express .= ' FIND_IN_SET('.$tag.',reject_tags) ';
                    }
                    $express .= ') ';
                    $output[] = new Expression($express);
                    break;
                default:
                    $output[] = ['stock_order_rejected.'.$field=>$item];
                    break;
            }
        }
        return $output;
    }

    /**
     * 标记Item为次品
     * @param $item
     */
    public function markItemAsRejects($item,$post){
        $reject_tags = $post['reject_tags'];
        $rejected = new StockOrderRejected();
        $rejected->order_id = $item->order_id;
        $rejected->item_id = $item->id;
        $rejected->product_id = $item->product_id;
        $rejected->item_type = $item->item_type;
        $rejected->sku = $item->sku;
        $rejected->product_type = $item->size_type;
        $rejected->size_us = $item->size_us;
        $rejected->has_engravings = $item->has_engravings;
        $rejected->engravings = $item->engravings;
        $rejected->item_status = 'rejected';
        $rejected->reject_tags = $reject_tags;
        $rejected->report_uid = $this->getCurrentUid();
        //一次只能标记一个
        $rejected->qty_rejected = $rejected->qty_rejected+1;
        $rejected->save();
    }

    /**
     * 次品解决
     * @param $id
     * @param $number
     */
    public function solved($id,$number){
        $model = self::findOne($id);
        if($model){
            $model->qty_solved += $number;
            if($model->qty_rejected<=$model->qty_solved){
                $model->item_status = self::STATUS_SOLVED;
                $model->solved_uid = $this->getCurrentUid();
                $model->solved_at = time();
            }
            $model->save();
            $item = Item::findOne($model->item_id);
            $item->has_rejects = 0;
            $item->save();
        }
    }
}