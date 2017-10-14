<?php
/**
 * Service.php 
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/8/4
 */

namespace app\models\service;


use app\models\BaseModel;
use app\models\User;
use renk\yiipal\helpers\Url;
use yii\db\Expression;

class ServiceIssue extends BaseModel{

    const STATUS_PENDING = 'pending';
    const STATUS_SOLVED = 'solved';

    public $categories = [];
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'service_issue';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = [
            [$this->getTableSchema()->getColumnNames(),'safe'],
            [['from','ext_order_id','sku','description'],'required'],
        ];

        return $rules;
    }

    public function attributeLabels()
    {
        return [
            'from' => '出处',
            'tags' => '问题归类',
            'customer_name' => '客户名称',
            'customer_email' => '客户邮箱',
            'customer_tel' => '客户电话',
            'ext_order_id' => '订单编号',
            'sku' => 'SKU',
            'description' => '问题描述',
            'solution' => '解决方案',
        ];
    }

    public function getItems(){
        return $this->hasMany(ServiceIssueItem::className(),['target_id'=>'id']);
    }

    public function getReportUser(){
        return $this->hasOne(User::className(),['id'=>'report_uid']);
    }

    public function getSolvedUser(){
        return $this->hasOne(User::className(),['id'=>'solved_uid']);
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
        $currentUid = $this->getCurrentUid();
        $buttons = [];
        $buttons[] = [
            'label' => '查看',
            'url' => Url::toRoute(['view-issue', 'id' => $this->id]),
            'icon' => 'glyphicon glyphicon-wrench',
            'attr' => ['class' => 'btn-order-view'],
        ];

        if($this->report_uid == $currentUid){
            $buttons[] = [
                'label' => '编辑',
                'url' => Url::toRoute(['update-issue', 'id' => $this->id]),
                'icon' => 'glyphicon glyphicon-wrench',
                'attr' => ['class' => 'btn-service-issue-update'],
            ];
        }

        if($this->report_uid == $currentUid && $this->status == self::STATUS_PENDING){
            $buttons[] = [
                'label' => '处理',
                'url' => Url::toRoute(['handle-issue', 'id' => $this->id]),
                'icon' => 'glyphicon glyphicon-wrench',
                'attr' => ['class' => 'btn-service-issue-update'],
            ];
        }

        return $buttons;
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
            if(preg_match('/.*_at$/',$key)){
                $date = explode("/", $item[$key]);
                if(count($date)==1){
                    $query->andFilterWhere($item);
                }else{
                    $query->andFilterWhere(['>',$key,strtotime($date[0].' 00:00:00')]);
                    $query->andFilterWhere(['<',$key,strtotime($date[1]." 23:59:59")]);
                }

            }else{
                $query->andWhere($item);
            }
        }
        if($defaultParams){
            foreach($defaultParams as $defaultParam){
                $query->andFilterWhere($defaultParam);
            }
        }
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
                case 'report_uid':
                    $uid = 0;
                    $user = User::find()->where(['nick_name'=>$item])->one();
                    $uid = $user?$user->id:0;
                    $output[] = [$field=>$uid];
                    break;
                case 'tags':
                    $tags = explode(",",$item);
                    $express = ' (';
                    foreach($tags as $index =>$tag){
                        if(empty($tag)) continue;
                        $express .= $index>0?' OR ':'';
                        $express .= ' FIND_IN_SET('.$tag.',tags) ';
                    }
                    $express .= ') ';
                    $output[] = new Expression($express);
                    break;
                default:
                    $output[] = [$field=>$item];;
            }

        }
        return $output;
    }
}