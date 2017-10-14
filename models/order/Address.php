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
use app\models\Website;
use renk\yiipal\helpers\Url;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

class Address extends BaseModel{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'order_address';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = [
            [$this->getTableSchema()->getColumnNames(),'safe'],
            [['region','postcode','street','city','country_id','firstname','lastname','email','telephone'],'required','on'=>['create']],
        ];
        return $rules;
    }

    /**
     * 设置Label
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'region' => '地区',
            'postcode' => '邮编',
            'street' => '街道',
            'city' => '城市',
            'country_id' => '国家',
            'firstname' => 'First Name',
            'lastname' => 'Last Name',
            'email' => '邮箱',
            'telephone' => '电话',
        ];
    }

    /**
     * 修改地址
     */
    public function changeAddress(){
        parent::save();
        $order = Order::findOne($this->parent_id);
        if($order->status !=Item::TASK_STATUS_PENDING){
            $order->last_track_status = Order::TASK_STATUS_ADDRESS_CHANGED;
        }
        $order->save();
        Order::archiveOrder($order->id,'修改收货地址');
        $this->synchronize($order);
    }

    /**
     * 同步地址
     */
    private function synchronize($order)
    {
        $website = Website::findOne($order->store_id);
        $url = $website->url.'/erp/order/address';
        $post_data = array(
            "id" => $order->increment_id,
            'country_id' => $this->country_id,
            'city' => $this->city,
            'region' => $this->region,
            'company' => $this->company,
            'firstname' => $this->firstname,
            'middlename' => $this->middlename,
            'lastname' => $this->lastname,
            'telephone' => $this->telephone,
            'email' => $this->email,
            'street' => $this->street,
            'postcode' => $this->postcode,
        );

        try {
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);

            $result = curl_exec($ch);
            curl_close($ch);

        } catch(Exception $e) {

        }
    }
}