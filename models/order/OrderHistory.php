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
use app\models\product\Product;
use app\models\product\Stock;
use app\models\User;
use renk\yiipal\helpers\Url;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

class OrderHistory extends BaseModel{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'order_history';
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

    public function getUser(){
        return $this->hasOne(User::className(), ['id' => 'uid']);
    }
}