<?php
/**
 * ShipmentFeeGroup.php 
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/11/1
 */

namespace app\models\shipment;


use app\models\BaseModel;
use app\models\order\Order;
use yii\data\ActiveDataProvider;
use yii\web\UploadedFile;

class ShipmentFeeGroup extends BaseModel{

    public $shipping_track_no = '';
    public $shipped_at = '';
    public $ext_order_id = '';


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'shipment_fee_group';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['shipping_track_no','shipped_at','ext_order_id'], 'safe'];
        return $rules;
    }

    /**
     * 获取运单信息
     * @return \yii\db\ActiveQuery
     */
    public function getItems(){
        return $this->hasMany(ShipmentFeeItem::className(),['group_id'=>'id']);
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
        $query = $class::find()->leftjoin("shipment_fee_item","shipment_fee_item.group_id=shipment_fee_group.id")
            ->distinct();
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
            $itemParams = ['shipping_track_no','shipped_at','ext_order_id'];
            switch($field){
                case in_array($field, $itemParams):
                    $field = 'shipment_fee_item.'.$field;
                    if($field == 'shipment_fee_item.ext_order_id'){
                        $val = strtolower($item);
                        if($val == 'yes') {
                            $output[] = ['is not', $field, null];
                        }elseif($val == 'no'){
                            $output[] = ['is', $field, null];
                        }else{
                            $output[] = [$field=>$item];
                        }
                    }else{
                        $output[] = [$field=>$item];
                    }
                    break;
                default:
                    $output[] = ["shipment_fee_group.".$field=>$item];
            }
        }
        return $output;
    }

    /**
     * 获取物流费用
     * @return array
     */
    public function getCostInfo(){
        $costInfo = [];
        $query = ShipmentFeeItem::find();
        $methods = ['UPS','DHL','EUB','ARAMEX'];
        foreach($methods as $method){
            $costInfo[$method] = $query->where(['shipping_method'=>$method])->sum("price");
        }
        $costInfo['total'] = array_sum($costInfo);
        return $costInfo;
    }

    /**
     * 创建Group
     * @param $paid
     * @return ShipmentFeeGroup
     */
    public function createGroup(){
        $batchNumber = ShipmentFeeGroup::find()->max('batch_number');
        if(empty($batchNumber)){
            $batchNumber = date("Ym").'01';
        }else{
            $batchNumber++;
        }
        $group = new ShipmentFeeGroup();
        $group->batch_number = $batchNumber;
        $group->uid = $this->getCurrentUid();
        $group->save();
        return $group;
    }

    /**
     * 导入物流单号
     * @param bool $runValidation
     * @param null $attributeNames
     */
    public function import(){
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

        $group = $this->createGroup();
        $line = 0;
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if( !$data) {
                continue;
            }

            if(!$line){
                $line++;
                continue;
            }

            $model = new ShipmentFeeItem();
            $model->group_id = $group->id;
            if( isset($data[0]) && !empty($data[0]) ) {
                $model->shipping_method = $data[0];
            }

            if( isset($data[1]) && !empty($data[1]) ) {
                $model->shipping_track_no = $data[1];
                $order = Order::find()->where(['shipping_track_no'=>$model->shipping_track_no])->one();
                if($order){
                    $model->order_id = $order->id;
                    $model->ext_order_id = $order->ext_order_id;
                    $model->increment_id = $order->increment_id;
                }
            }

            if( isset($data[2]) && !empty($data[2]) ) {
                $model->price = $data[2];
            }

            if( isset($data[3]) && !empty($data[3]) ) {
                $model->shipped_at = strtotime($data[3]);
            }

            $model->save();
        }
        fclose($handle);
        $group->shipping_method = $model->shipping_method;
        $group->save();
        return true;
    }
}