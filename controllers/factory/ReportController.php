<?php
/**
 * ReportController.php 
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/7/6
 */

namespace app\controllers\factory;


use app\controllers\BaseController;
use app\models\order\Item;
use app\models\order\StockOrder;
use app\models\order\StockOrderRejected;
use app\models\product\ProductDelivered;
use app\models\ProductRejects;

class ReportController extends BaseController {
    public function actionIndex(){
        if($this->isPost()){
            $data = [];
            $dateRange = $this->post('date_range',false);
            $startTime = strtotime(date("Y-m-d"));
            $endTime = strtotime(date("Y-m-d")." +1 day");
            if($dateRange){
                $queryDate = explode("/",$dateRange);
                $startTime = strtotime($queryDate[0]);
                $endTime = strtotime($queryDate[1]." +1 day");
            }
            $customQty = Item::find()->leftJoin('order','`order`.id = `order_item`.order_id')
                        ->where(['not in','order_item.item_status',['cancelled','pending']])
                        ->andWhere(['order_item.item_type'=>'custom'])
                        ->andWhere(['not in','order.status',['cancelled','pending']])
                        ->andWhere(['>','order.process_at',$startTime])
                        ->andWhere(['<','order.process_at',$endTime])
                        ->sum('order_item.qty_ordered')
                        ;

            $customQtyPassed = ProductDelivered::find()
                            ->where(['order_type'=>'custom'])
                            ->andWhere(['>','created_at',$startTime])
                            ->andWhere(['<','created_at',$endTime])
                            ->sum('qty_passed')
                        ;

            $customQtyRejects = StockOrderRejected::find()
                ->where(['item_type'=>'custom'])
                ->andWhere(['>','created_at',$startTime])
                ->andWhere(['<','created_at',$endTime])
                ->sum('qty_rejected')
            ;

            $customQtySolved = StockOrderRejected::find()
                ->where(['item_type'=>'custom'])
                ->andWhere(['>','created_at',$startTime])
                ->andWhere(['<','created_at',$endTime])
                ->sum('qty_solved')
            ;

            $stockQty   = StockOrder::find()
                            ->where(['item_type'=>'custom'])
                            ->andWhere(['>','created_at',$startTime])
                            ->andWhere(['<','created_at',$endTime])
                            ->sum('qty_ordered')
                        ;

            $stockQtyPassed = ProductDelivered::find()
                            ->where(['order_type'=>'stock'])
                            ->andWhere(['>','created_at',$startTime])
                            ->andWhere(['<','created_at',$endTime])
                            ->sum('qty_passed')
                        ;

            $stockQtyRejects = StockOrderRejected::find()
                ->where(['item_type'=>'stockup'])
                ->andWhere(['>','created_at',$startTime])
                ->andWhere(['<','created_at',$endTime])
                ->sum('qty_rejected')
            ;

            $stockQtySolved = StockOrderRejected::find()
                ->where(['item_type'=>'stockup'])
                ->andWhere(['>','created_at',$startTime])
                ->andWhere(['<','created_at',$endTime])
                ->sum('qty_solved')
            ;
            $data['custom_qty'] = intval($customQty);
            $data['custom_qty_passed'] = intval($customQtyPassed);
            $data['custom_qty_rejects'] = intval($customQtyRejects);
            $data['custom_qty_solved'] = intval($customQtySolved);

            $data['stock_qty'] = intval($stockQty);
            $data['stock_qty_passed'] = intval($stockQtyPassed);
            $data['stock_qty_rejects'] = intval($stockQtyRejects);
            $data['stock_qty_solved'] = intval($stockQtySolved);
            return $this->json_output(['data'=>$data]);
        }else{
            return $this->render('index', [
            ]);
        }

    }
}