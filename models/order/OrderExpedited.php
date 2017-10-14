<?php
/**
 * OrderExpedited.php
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/5/19
 */

namespace app\models\order;

use app\models\BaseModel;

class OrderExpedited extends BaseModel{
    // 正常订单
    const TASK_STATUS_NORMAL		= 'normal';
    //等待确认
    const TASK_STATUS_WAIT_CONFIRM	= 'wait_confirm';
    //已经确认
    const TASK_STATUS_CONFIRMED	= 'confirmed';
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'order_factory_expedited';
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
}