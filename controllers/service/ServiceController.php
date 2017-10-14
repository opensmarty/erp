<?php
namespace app\controllers\service;
use app\controllers\BaseController;
use app\models\Category;
use app\models\service\ServiceIssue;
use app\models\service\ServiceIssueItem;
use app\models\service\ServiceIssueSolution;
use yii;
class ServiceController extends BaseController{

    /**
     * 客服问题分类
     * @return string
     */
    public function actionIssueTags(){
        return $this->render('tags');
    }

    /**
     * 问题列表
     * @return string
     */
    public function actionIssueList(){
        $categories = Category::find()->indexBy('id')->asArray()->all();
        $searchModel = new ServiceIssue();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $models = $dataProvider->getModels();
        foreach($models as &$model){
            $model->categories = $categories;
        }
        $gets = $this->get('ServiceIssue',[]);
        $tags = isset($gets['tags'])?$gets['tags']:'';
        return $this->render('issue-list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'tags' => $tags,
        ]);
    }

    /**
     * 查看问题详情
     * @param $id
     * @return string
     */
    public function actionViewIssue($id){
        $model = ServiceIssue::findOne($id);
        $items = ServiceIssueItem::findAll(['issue_id'=>$id]);
        return $this->render('view', [
            'model' => $model,
            'items' => $items,
        ]);
    }

    /**
     * 添加问题
     * @return string|yii\web\Response
     */
    public function actionCreateIssue(){
        $model = new ServiceIssue();
        $issueItem = new ServiceIssueItem();
        if ($model->load(Yii::$app->request->post())) {
            $model->report_uid = $this->getCurrentUid();
            $model->save();
            return $this->redirect(['issue-list']);
        }else{
            return $this->render('update', [
                'model' => $model,
                'issueItem' => $issueItem,
            ]);
        }
    }

    /**
     * 更新问题
     * @param $id
     * @return string|yii\web\Response
     */
    public function actionUpdateIssue($id){
        $model = ServiceIssue::findOne($id);
        if ($model->load(Yii::$app->request->post())) {
            $model->report_uid = $this->getCurrentUid();
            $model->save();
            return $this->redirect(['issue-list']);
        }else{
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * 处理问题
     * @param $id
     * @return string|yii\web\Response
     */
    public function actionHandleIssue($id){
        $model = new ServiceIssueItem();
        if ($model->load(Yii::$app->request->post())) {
            $model->issue_id = $id;
            $model->save();
            if($this->post('solved',0)){
                $issue = ServiceIssue::findOne($id);
                $issue->solved_uid = $this->getCurrentUid();
                $issue->status = ServiceIssue::STATUS_SOLVED;
                $issue->save();
            }
            return $this->redirect(['issue-list']);
        }
        $items = ServiceIssueItem::find()->where(['issue_id'=>$id])->orderBy('created_at DESC')->all();
        return $this->render('handle-issue', [
            'model' => $model,
            'items' => $items,
            'solutions' => $this->getSolutions($id),
        ]);
    }

    /**
     * 获取针对指定的问题的处置建议.
     * @param $issueId
     * @return array|yii\db\ActiveRecord[]
     */
    private function getSolutions($issueId){
        $issue = ServiceIssue::find()->where(['id'=>$issueId])->one();
        $tags = explode(",",$issue->tags);
        $query = ServiceIssueSolution::find();
        $express = ' (';
        foreach($tags as $index =>$tag){
            if(empty($tag)) continue;
            $express .= $index>0?' OR ':'';
            $express .= ' FIND_IN_SET('.$tag.',tags) ';
        }
        $express .= ') ';
        $query->where(new yii\db\Expression($express));
        $solutions = $query->all();
        return $solutions;
    }

    /**
     * 客服问题指南列表
     * @return string
     */
    public function actionSolutionList(){
        $searchModel = new ServiceIssueSolution();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $gets = $this->get('ServiceIssueSolution',[]);
        $tags = isset($gets['tags'])?$gets['tags']:'';
        return $this->render('solution-list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'tags' => $tags,
        ]);
    }

    /**
     * 创建客户指南
     * @return string|yii\web\Response
     */
    public function actionSolutionCreate(){
        $model = new ServiceIssueSolution();
        if ($model->load(Yii::$app->request->post())) {
            $model->save();
            return $this->redirect(['solution-list']);
        }else{
            return $this->render('solution-update', [
                'model' => $model,
            ]);
        }
    }

    public function actionSolutionUpdate($id){
        $model = ServiceIssueSolution::findOne($id);
        if ($model->load(Yii::$app->request->post())) {
            $model->save();
            return $this->redirect(['solution-list']);
        }else{
            return $this->render('solution-update', [
                'model' => $model,
            ]);
        }
    }

    public function actionSolutionView($id){
        $model = ServiceIssueSolution::findOne($id);
        return $this->render('solution-view', [
            'model' => $model,
        ]);
    }

    public function actionSolutionDelete($id){
        $this->findModel(ServiceIssueSolution::className(),$id)->delete();
        return $this->redirect(['solution-list']);
    }
}
