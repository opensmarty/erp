<?php
/**
 * Material.php 
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/10/26
 */

namespace app\models\supplies;


use app\models\BaseModel;
use app\models\File;
use app\models\order\Order;
use yii\data\ActiveDataProvider;
use yii\helpers\Url;
use yii\web\UploadedFile;

class Material extends BaseModel{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'packing_material';
    }

    public function attributeLabels()
    {
        return [
            'name' => '名称',
            'files' => '图片',
            'quantity' => '库存',
            'price' => '价格',
            'type' => '类型',
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['files'], 'file', 'skipOnEmpty' => true, 'maxFiles' => 8];
        return $rules;
    }

    /**
     * 列表也下拉按钮
     * @return array
     */
    public function buttons(){

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

        return $buttons;
    }

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
     * 获取耗材订单
     * @return \yii\db\ActiveQuery
     */
    public function getPacking(){
        return $this->hasMany(Packing::className(),['material_id'=>'id'])
            ->where('qty > qty_delivered');
    }


    /**
     * 保存
     * @param bool $runValidation
     * @param null $attributeNames
     */
    public function save($runValidation = true, $attributeNames = null){
        if($this->files && UploadedFile::getInstances($this,'files')){
            $fileModel = new File();
            $path = 'packing/material';
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
        $query = $class::find()->with('packing');
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
     * 扣减耗材
     * @param $ids
     */
    public function reduceMaterialByOrderIds($ids){
        $materialList = $this->getMaterialList($ids);
        foreach($materialList as $type=>$number){
            $this->reduceMaterialItem($type,$number);
        }
    }

    /**
     * 减少耗材元素库存数量
     * @param $type
     * @param $number
     */
    private function reduceMaterialItem($type,$number){
        $material = Material::find()->where(['type'=>$type])->one();
        $material->quantity -=$number;
        $material->save();
    }

    /**
     * 获取耗材
     * @param $ids
     * @return array
     */
    private function getMaterialList($ids){
        $orders = Order::find()->where(['in','id',$ids])->with('items')->all();
        $materialList = [];
        foreach($orders as $order){
            $material = [
                'drawer_single'     => 0,
                'drawer_sets'     => 0,
                'drawer_double'     => 0,
                'bag'               => 0,
                'duster'            => 0,
                'card'              => 0,
                'ring_box_single'   => 0,
                'ring_box_sets'     => 0,
                'necklace_box'      => 0,
            ];

            $items = $order->items;
            foreach($items as $item){
                if(in_array($item->item_status,['shipped','cancelled'])) continue;
                $product = $item->product;
                if($product->getType() == "ring"){
                    //单戒
                    if($product->is_couple===0){
                        $material['ring_box_single'] += $item->qty_ordered;
                    }
                    //对戒
                    elseif($product->is_couple===1){
                        $material['ring_box_sets'] +=$item->qty_ordered/2;
                    }
                    //套戒
                    else{
                        $material['ring_box_sets'] += $item->qty_ordered;
                    }
                }else{
                    $material['necklace_box'] += $item->qty_ordered;
                }
            }

            $boxNumber = array_sum($material);
            $material['drawer_double'] = floor($boxNumber/2);
            if($material['drawer_double']>0){
                $material['drawer_sets'] = $boxNumber%2;
            }else{
                if($material['ring_box_single']>0){
                    $material['drawer_single'] = $boxNumber%2;
                }else{
                    $material['drawer_sets'] = $boxNumber%2;
                }
            }

            $drawerNumber = $material['drawer_double']+$material['drawer_single']+$material['drawer_sets'];
            $material['bag'] = $drawerNumber;
            $material['duster'] = $drawerNumber;
            $material['card'] = $drawerNumber;

            $materialList = $this->sumMaterials($materialList,$material);
        }
        return $materialList;
    }

    /**
     * 合并所有耗材数量
     * @param $materialList
     * @param $material
     * @return mixed
     */
    private function sumMaterials($materialList, $material){
        if(empty($materialList)){
            $materialList = $material;
            return $materialList;
        }
        foreach($materialList as $index=>$value){
            $materialList[$index] = $material[$index]+$value;
        }
        return $materialList;
    }
}