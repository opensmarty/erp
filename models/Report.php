<?php
/**
 * Report.php 
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/7/8
 */

namespace app\models;

use yii;
use yii\db\Query;

class Report extends BaseModel{

    public static function getSalesWithCategory($startTime,$endTime){
        $command = Yii::$app->db->createCommand('
                          SELECT p.`cid`, SUM(oi.`qty_ordered`) as qty,week(FROM_UNIXTIME(o.created_at,"%Y-%m-%d")) as group_date FROM `order_item` oi
                          INNER JOIN `product` p ON oi.`product_id`=p.`id`
                          INNER JOIN `order` o ON o.id=oi.`order_id`
                          WHERE oi.`item_status` NOT IN ("cancelled","pending")
                          AND o.`status` NOT IN ("cancelled","pending")
                          AND oi.`item_type`="custom"
                          AND o.created_at>:date_start
                          AND o.created_at<:date_end
                          GROUP BY week(FROM_UNIXTIME(o.created_at,"%Y-%m-%d")),p.`cid`')
                    ->bindValue(':date_start', $startTime)
                    ->bindValue(':date_end', $endTime)
                    ;
        $results = $command->queryAll();
        return $results;
    }


}