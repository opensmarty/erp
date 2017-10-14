<?php
/**
 * Packing.php 
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/10/26
 */

namespace app\models\supplies;


use app\models\BaseModel;
use yii\helpers\Url;

class PackingGroup extends BaseModel{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'packing_group';
    }

    public function attributeLabels()
    {
        return [
            'group_id' => '批次',
            'status' => '状态',
            'created_at' => '下单时间',
            'finished_at' => '结束时间',
        ];
    }

    /**
     * 获取订单item
     * @return \yii\db\ActiveQuery
     */
    public function getPacking(){
        return $this->hasMany(Packing::className(),['group_id'=>'id']);
    }

    /**
     * 创建耗材批次
     * @param $paid
     * @return mixed
     */
    public function createGroup($paid){
        $batchNumber = PackingGroup::find()->max('batch_number');
        if(empty($batchNumber)){
            $batchNumber = date("Ym").'01';
        }else{
            $batchNumber++;
        }
        $packingGroup = new PackingGroup();
        $packingGroup->batch_number = $batchNumber;
        $packingGroup->paid = $paid;
        $packingGroup->save();
        return $packingGroup->id;
    }

    public function getCostInfo($params=false){
        $query = Packing::find();
        if($params){
            foreach($params as $key=>$val){
                if(empty($val)) continue;
                $query->andWhere([$key=>$val]);
            }
        }

        $costInfo['unpaid'] = $query->sum("price*(qty-qty_delivered)");
        $query = PackingGroup::find();
        $costInfo['paid'] = $query->sum("paid");
        return $costInfo;
    }
}