<?php
/**
 * IssueController.php 
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/8/23
 */

namespace app\controllers\order;

use app\models\Category;
use yii;
use app\controllers\BaseController;
use app\models\order\Order;
use app\models\order\OrderIssue;
use app\models\product\Product;
use yii\helpers\ArrayHelper;

class IssueController extends BaseController {

    public function actionIndex(){
        $searchModel = new Order();
        $searchModel->leftJoinTables['order_issue'] = 'order_issue';
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams,null,[['in','order_issue.issue_status',['pending','solved','processing','wait_confirm']]]);
        $models = $dataProvider->getModels();
        $productIds = [];
        foreach($models as $model){
            foreach ($model->items as $item) {
                $productIds[] = $item->product_id;
            }
        }
        $products = Product::find()->where(['in','id',$productIds])->all();
        $products = ArrayHelper::index($products, 'id');
        $categories = Category::find()->indexBy('id')->asArray()->all();
        foreach($models as &$model){
            $model->products = $products;
            $model->categories = $categories;
        }
        $gets = $this->get('Order',[]);
        $tags = isset($gets['issue_tags'])?$gets['issue_tags']:'';
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'tags' => $tags,
        ]);
    }

    /**
     * 报告问题
     * @param $id
     */
    public function actionCreate($id){
        $order = Order::findOne($id);
        if(empty($order)){
            return $this->json_output(['status'=>0,'msg'=>'处理失败']);
        }
        $orderIssue = OrderIssue::findOne(['order_id'=>$order->id]);
        if(empty($orderIssue)){
            $orderIssue = new OrderIssue();
        }

        if($this->isPost()){
            $orderIssue->order_id = $order->id;
            $orderIssue->issue_status = 'pending';
            $orderIssue->solved_uid = '';
            $orderIssue->solved_at = '';
            $orderIssue->ext_order_id = $order->ext_order_id;
            $orderIssue->issue_tags = $this->post("issue_tags","");
            $orderIssue->report_uid = $this->getCurrentUid();
            $orderIssue->save();

            //阻止订单发货
            $order->blocked = 1;
            $order->save();
            return $this->json_output();
        }
        return $this->renderAjax('//fragment/order-issue',['modal'=>$orderIssue,'order'=>$order]);
    }

    /**
     * 解决订单问题
     * @param $id
     * @return array
     */
    public function actionSolved($id){
        $order = Order::findOne($id);
        if(empty($order)){
            return $this->json_output(['status'=>0,'msg'=>'处理失败']);
        }
        $order->blocked = 0;
        $order->save();
        $orderIssue = OrderIssue::findOne(['order_id'=>$order->id]);
        $orderIssue->issue_status = 'solved';
        $orderIssue->solved_uid = $this->getCurrentUid();
        $orderIssue->save();
        return $this->json_output();
    }

    /**
     * 标记问题解决中
     * @param $id
     * @return array
     */
    public function actionProcessing($id){
        $order = Order::findOne($id);
        if(empty($order)){
            return $this->json_output(['status'=>0,'msg'=>'处理失败']);
        }
        $orderIssue = OrderIssue::findOne(['order_id'=>$order->id]);
        $orderIssue->issue_status = 'processing';
        $orderIssue->solved_uid = $this->getCurrentUid();
        $orderIssue->save();
        return $this->json_output();
    }
}