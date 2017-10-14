<?php
/**
 * CronUpdateOrderPaymentStatus.php 
 *
 * @Author: Yangjianguo
 */

namespace app\models\cron;
use app\models\BaseModel;


class CronUpdateOrderPaymentStatus extends BaseModel{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'cron_update_order_payment_status';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = parent::rules();
        return $rules;
    }
}