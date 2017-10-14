<?php
/**
 * OrderIssue.php 
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/8/23
 */

namespace app\models\order;


use app\models\BaseModel;
use app\models\Comment;
use app\models\User;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\helpers\Url;

class OrderRefund extends BaseModel{
    public $products = [];
    public $categories = [];
    public $customer_name = '';
    public $customer_email = '';
    public $payment_status = '';
    public $status = '';
    public $sku = '';
    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = [
            [$this->getTableSchema()->getColumnNames(),'safe'],
            [['customer_name','customer_email','payment_status','status','sku'],'safe'],
        ];
        return $rules;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'order_refund';
    }

    public function getOrder(){
        return $this->hasOne(Order::className(),['id'=>'order_id']);
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
     * 获取订单中的Item项目
     * @return $this
     */
    public function getItems()
    {
        return $this->hasMany(Item::className(), ['order_id' => 'order_id']);
    }

    /**
     * 获取订单备注
     * @return $this
     */
    public function getComments(){
        $uid = \Yii::$app->user->id;
        return $this->hasMany(Comment::className(), ['target_id' => 'order_id'])
            ->where('type=:threshold1 AND FIND_IN_SET(:threshold2, visible_uids)', [':threshold1' => 'order',':threshold2' => $uid])
            ;
    }

    /**
     * 获取订单的地址信息
     * @return $this
     */
    public function getAddress(){
        return $this->hasOne(Address::className(), ['parent_id' => 'order_id'])
            ->where('address_type = :threshold', [':threshold' => 'shipping'])
            ;
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
            'url'=>Url::toRoute(['/order/order/view','id'=>$this->order_id]),
            'icon'=>'glyphicon glyphicon-wrench',
            'attr' =>['class'=>'btn-order-view'],
        ];

        $buttons[] = [
            'label'=>'备注',
            'url'=>Url::toRoute(['/comment/create?target_id='.$this->order_id.'&type=order&subject='.Comment::COMMENT_TYPE_OTHERS]),
            'icon'=>'glyphicon glyphicon-wrench',
            'attr' =>['class'=>'btn-order-expedite ajax-modal'],
        ];

        $buttons[] = [
            'label'=>'已退款',
            'url'=>Url::toRoute(['/order/refund/solved?id='.$this->id]),
            'icon'=>'glyphicon glyphicon-wrench',
            'attr' =>['class'=>'btn-order-issue ajax'],
        ];


        return $buttons;
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
        $query = $class::find()->with('items')->with('comments')->with("address")
            ->leftJoin('order','order_refund.order_id = order.id')
            ->leftJoin('order_item','order_item.order_id = order.id')
            ->leftJoin('comment',"comment.target_id = order.id AND comment.type='order'")
            ->leftJoin('order_address',"order_address.parent_id = order.id AND order_address.address_type='shipping'")
            ->distinct();

        $websites = \Yii::$app->user->getIdentity()->websites;
        if(!empty($websites)){
            $query->andWhere(['in','order.source',explode(",",$websites)]);
        }

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
                'shipped_at','from','source','coupon_code'];
            $itemParams = ['item_type','sku','has_engravings','has_rejects'];
            $addressParams = ['customer_name','customer_email'];
            $commentParams = ['has_comment'];
            $orderRefundParams = ['solved_uid','report_uid','refund_status','refund_tags','created_at','solved_at'];
            switch($field){
                case 'last_track_status':
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
                    $field = 'order_item.'.$field;
                    $output[] = [$field=>$item];
                    break;

                case in_array($field, $orderRefundParams):
                    if(in_array($field,['solved_uid','report_uid'])){
                        $user = User::find()->where(['nick_name'=>$item])->one();
                        if($user){
                            $uid = $user->id;
                        }else{
                            $uid = -1;
                        }
                        $field = 'order_refund.'.$field;
                        $output[] = [$field=>$uid];
                    }elseif($field === 'refund_tags'){
                        $tags = explode(",",$item);
                        $express = ' (';
                        foreach($tags as $index =>$tag){
                            if(empty($tag)) continue;
                            $express .= $index>0?' OR ':'';
                            $express .= ' FIND_IN_SET('.$tag.',refund_tags) ';
                        }
                        $express .= ') ';
                        $output[] = new Expression($express);
                    }else{
                        $output[] = ['order_refund.'.$field=>$item];
                    }

                    break;
                case in_array($field, $commentParams):
                    $field = 'comment.target_id';
                    if($item == 1){
                        $output[] = new Expression(" comment.target_id IS NOT null ");
                    }else{
                        $output[] = new Expression(" comment.target_id IS null ");
                    }
                    break;
                case in_array($field, $addressParams):
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
}