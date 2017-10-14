<?php

namespace app\models\product;

use app\models\BaseModel;
use app\models\File;
use app\models\order\Item;
use app\models\order\StockOrder;
use renk\yiipal\helpers\ArrayHelper;

class Stock extends BaseModel
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'stock';
    }

    /**
     * 获取Item的产品信息
     * @return \yii\db\ActiveQuery
     */
    public function getProduct(){
        return $this->hasOne(Product::className(), ['id' => 'product_id']);
    }

    /**
     * 获取产品库存信息
     * @param $product
     * @return array
     */
    public function getStocksByProduct($product){
        $output = [];
        if($product->cid==2 && $product->is_couple ==1){
            $results = self::findAll(['product_id'=>$product->id,'type'=>'men']);
            $stocks = ArrayHelper::index($results,'size_id');
            $output['men'] = $stocks;
            $results = self::findAll(['product_id'=>$product->id,'type'=>'women']);
            $stocks = ArrayHelper::index($results,'size_id');
            $output['women'] = $stocks;
        }else{
            $results = self::findAll(['product_id'=>$product->id,'type'=>'none']);
            $stocks = ArrayHelper::index($results,'size_id');
            $output = $stocks;
        }

        return $output;
    }

    /**
     * 加添库存
     * @param $product
     * @param $posts
     * @param $sizes
     */
    public function addStocks($product,$posts,$sizes){
        $sizes = ArrayHelper::index($sizes, 'id');
        //项链、手环
        if($product->cid!=3){
            return $this->saveOtherProducts($product, $posts);
        }
        //戒指
        if($product->is_couple==1){
            return $this->saveStocksForCoupleRings($product,$posts,$sizes);
        }else{
            return $this->saveStocksForSingleRing($product,$posts,$sizes);
        }
    }

    /**
     * 修改库存
     * @param $product
     * @param $posts
     * @param $sizes
     */
    public function editStocks($product,$posts,$sizes){
        $sizes = ArrayHelper::index($sizes, 'id');
        //项链、手环
        if($product->cid!=3){
            $stock = self::find()->where(['product_id'=>$product->id,'type'=>'none','size_code'=>0])->one();
            if(empty($stock)){
                $stock = new Stock();
                $stock->product_id = $product->id;
                $stock->type = 'none';
                $stock->total = 0;
                $stock->size_code = 0;
                $stock->uid = \Yii::$app->user->id;
            }
            if($stock){
                $stock->total = $posts['number'];
                $stock->save();
            }
            return true;
        }

        //戒指-对戒
        if($product->is_couple==1){
            $types = ['men','women'];
            foreach($types as $type){
                foreach($posts[$type] as $sizeId => $number){
                    $stock = self::find()->where(['product_id'=>$product->id,'size_id'=>$sizeId,'type'=>$type])->one();
                    if(empty($stock)){
                        $stock = new Stock();
                        $stock->product_id = $product->id;
                        $stock->size_id = $sizeId;
                        $stock->size_code = $sizes[$sizeId]->size;
                        $stock->type = $type;
                        $stock->total = 0;
                        $stock->uid = \Yii::$app->user->id;
                    }
                    $stock->total = $number;
                    $stock->save();
                }
            }
        }
        //戒指-单戒
        else{
            foreach($posts['size'] as $sizeId => $number){
                $stock = self::find()->where(['product_id'=>$product->id,'size_id'=>$sizeId,'type'=>'none'])->one();
                if(empty($stock)){
                    $stock = new Stock();
                    $stock->product_id = $product->id;
                    $stock->size_id = $sizeId;
                    $stock->size_code = $sizes[$sizeId]->size;
                    $stock->type = 'none';
                    $stock->total = 0;
                    $stock->uid = \Yii::$app->user->id;
                    $stock->save();
                }
                $stock->total = $number;
                $stock->save();
            }
        }
    }

    /**
     * 订单过来后减少对应的库存数量.
     * @param $product
     * @param int $number
     * @param string $sizeUs
     * @param string $sizeType
     */
    public static function reduceStocks($product,$number=0,$sizeUs='',$sizeType='none'){
        $stock = self::find()->where(['product_id'=>$product->id,'type'=>$sizeType,'size_code'=>$sizeUs])->one();
        if($stock){
            $stock->total -= $number;
            $stock->save();
        }
    }


    /**
     * 增加库存数量
     * @param $product
     * @param $sizeType
     * @param $sizeUs
     * @param $number
     */
    public static function increaseStocks($product, $number,$sizeUs='',$sizeType='none'){
        $stock = self::find()->where(['product_id'=>$product->id,'type'=>$sizeType,'size_code'=>$sizeUs])->one();
        if($stock){
            $stock->total += $number;
            $stock->save();
        }
    }

    /**
     * 项链、手环等没有尺寸的产品添加库存
     * @param $product
     * @param $posts
     */
    private function saveOtherProducts($product, $posts){
        $number = $posts['number'];
        if($number>0){
            if($number>0){
                $stockOrder = new StockOrder();
                $stockOrder->addStocks($product,'none',0,$number);
            }
        }

        $stock = self::find()->where(['product_id'=>$product->id,'type'=>'none'])->one();
        if(empty($stock)){
            $stock = new Stock();
            $stock->product_id = $product->id;
            $stock->type = 'none';
            $stock->total = 0;
            $stock->size_code = 0;
            $stock->uid = \Yii::$app->user->id;
            $stock->save();
        }
    }

    /**
     * 普通戒指加库存
     * @param $product
     * @param $posts
     * @param $sizes
     */
    private function saveStocksForSingleRing($product, $posts, $sizes){
        foreach($posts['size'] as $sizeId => $number){
            if($number>0){
                if($number>0){
                    $stockOrder = new StockOrder();
                    $stockOrder->addStocks($product,'none',$sizes[$sizeId]->size,$number);
                }
            }

            $stock = self::find()->where(['product_id'=>$product->id,'size_id'=>$sizeId,'type'=>'none'])->one();
            if(empty($stock)){
                $stock = new Stock();
                $stock->product_id = $product->id;
                $stock->size_id = $sizeId;
                $stock->size_code = $sizes[$sizeId]->size;
                $stock->type = 'none';
                $stock->total = 0;
                $stock->uid = \Yii::$app->user->id;
                $stock->save();
            }
        }
    }

    /**
     * 对戒加库存
     * @param $product
     * @param $posts
     * @param $sizes
     */
    private function saveStocksForCoupleRings($product, $posts, $sizes){
        $types = ['men','women'];
        foreach($types as $type){
            if(!isset($posts[$type]))continue;
            foreach($posts[$type] as $sizeId => $number){

                if($number>0){
                    $stockOrder = new StockOrder();
                    $stockOrder->addStocks($product,$type,$sizes[$sizeId]->size,$number);
                }

                $stock = self::find()->where(['product_id'=>$product->id,'size_id'=>$sizeId,'type'=>$type])->one();
                if(empty($stock)){
                    $stock = new Stock();
                    $stock->product_id = $product->id;
                    $stock->size_id = $sizeId;
                    $stock->size_code = $sizes[$sizeId]->size;
                    $stock->type = $type;
                    $stock->total = 0;
                    $stock->uid = \Yii::$app->user->id;
                    $stock->save();
                }
            }
        }
    }
}
