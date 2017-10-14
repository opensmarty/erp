<?php
/**
 * TemplateController.php 
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/8/11
 */

namespace app\controllers\product;

use app\models\Comment;
use app\models\product\Product;
use app\models\product\ProductTemplateAttributes;
use app\models\User;
use renk\yiipal\components\ExportData;
use renk\yiipal\helpers\FileHelper;
use Yii;
use app\controllers\BaseController;
use app\models\product\ProductTemplate;

class TemplateController extends BaseController{
    /**
     * 开版列表
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new ProductTemplate();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams,null,[['in','status',['finished','cancelled']]]);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * 选品列表
     * @return string
     */
    public function actionProcessList()
    {
        $searchModel = new ProductTemplate();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams,null,[['not in','status',['finished','cancelled']]]);
        return $this->render('process-list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * 工厂开版列表
     * @return string
     */
    public function actionFactoryIndex()
    {
        $searchModel = new ProductTemplate();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams,null,[['type'=>'0'],['not in','status',['pending','cancelled','finished']]]);
        return $this->render('factory-index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * 渲染列表
     * @return string
     */
    public function actionStudioIndex()
    {
        $searchModel = new ProductTemplate();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams,null,[['in','status',['repair_pending','repair','confirm_pending','accepted_pending','studio_pending','studio_start']]]);
        return $this->render('factory-studio', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * 渲染费用列表
     * @return string
     */
    public function actionStudioPriceList(){

        $dateRange = false;
        $params = $this->get('ProductTemplate',[]);
        if(isset($params['finished_at'])){
            $dateRange = $params['finished_at'];
        }
        $searchModel = new ProductTemplate();
        $staticInfo = $searchModel->getRenderCostInfo($dateRange);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams,null,[['status'=>'finished']]);
        return $this->render('studio-price-list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'staticInfo' => $staticInfo,
        ]);
    }

    /**
     * 删除版图.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel(ProductTemplate::className(),$id)->delete();
        return $this->redirect(['index']);
    }

    /**
     * 版图处理时间线
     * @param $id
     * @return string
     */
    public function actionProcessTimeline($id){
        $template = ProductTemplate::findOne($id);
        return $this->renderAjax('timeline', [
            'model' => $template,
        ]);
    }

    /**
     * 修改产品版号
     * @param $id
     * @return array
     */
    public function actionEditTemplateNo(){
        $id = $this->post('pk');
        $value = $this->post('value');
        $model = ProductTemplate::findOne($id);
        if($model){
            $model->template_no = trim($value);
            $model->reason_note = $model->reason_note?:"无";
            $model->save();
            return $this->json_output();
        }else{
            return $this->json_output(['status'=>0,'msg'=>'处理失败']);
        }
    }

    /**
     * 修改产品价格,只能修改一次
     * @param $id
     * @return array
     */
    public function actionEditOncePrice(){
        $id = $this->post('pk');
        $value = $this->post('value');
        $model = ProductTemplate::findOne($id);
        if($model){
            if(empty($model->render_price)){
                $model->render_price = trim($value);
                $model->save();
            }
            return $this->json_output();
        }else{
            return $this->json_output(['status'=>0,'msg'=>'处理失败']);
        }
    }

    /**
     * 修改产品价格
     * @param $id
     * @return array
     */
    public function actionEditPrice(){
        $id = $this->post('pk');
        $value = $this->post('value');
        $model = ProductTemplate::findOne($id);
        if($model){
            $model->render_price = trim($value);
            $model->save();
            return $this->json_output();
        }else{
            return $this->json_output(['status'=>0,'msg'=>'处理失败']);
        }
    }

    /**
     * 修改渲染类型
     * @param $id
     * @return array
     */
    public function actionEditRenderType(){
        $id = $this->post('pk');
        $value = $this->post('value');
        $model = ProductTemplate::findOne($id);
        if($model){
            $model->render_type = trim($value);
            $model->save();
            return $this->json_output();
        }else{
            return $this->json_output(['status'=>0,'msg'=>'处理失败']);
        }
    }

    /**
     * 查看开版信息
     * @param $id
     */
    public function actionView($id){
        $model = ProductTemplate::findOne($id);
        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * 添加开版
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new ProductTemplate();
        $model->fids = rtrim($this->post('based_fids',''),",");
        $model->template_no = trim($this->post('template_no',""));
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            //保存备注
            $comment = $this->post('comment','');
            $commentModel = new Comment();
            $commentModel->type = 'template';
            $commentModel->subject = Comment::COMMENT_TYPE_DESCRIPTION;
            $commentModel->target_id = $model->getPrimaryKey();
            $commentModel->content = $comment;
            $commentModel->uid = $this->getCurrentUid();
            $commentModel->visible_uids = '';
            $users = User::getRoleUsers(['admin','Backend-Manage','studio-manager','market','factory-template-member','rendering-member','factory-manger']);
            if($users){
                foreach($users as $user){
                    $commentModel->visible_uids .= $user->id.',';
                }
            }
            $commentModel->visible_uids = rtrim($commentModel->visible_uids,',');
            $commentModel->save();


            if($model->type == '1'){
                $product = Product::find()->where(['sku'=>$model->based_sku])->one();
                if($product){
                    $template = ProductTemplate::find()->where(['template_no'=>$product->template_no])->one();
                    if($template){
                        $templateAttributes = ProductTemplateAttributes::find()->where(['tpl_id'=>$template->id])->one();
                        if($templateAttributes){
                            $templateAttributes->id=0;
                            $templateAttributes->tpl_id=$model->id;
                            $templateAttributes->setIsNewRecord(true);
                            $templateAttributes->save();
                        }
                    }
                }

            }

            return $this->redirect(['process-list']);
        }else{
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * 编辑开版
     * @return string|\yii\web\Response
     */
    public function actionUpdate($id)
    {
        $model = ProductTemplate::findOne($id);
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $commentId = $this->post('comment_id','');
            $comment = $this->post('comment','');
            if(empty($commentId)){
                //保存备注
                $commentModel = new Comment();
                $commentModel->type = 'template';
                $commentModel->subject = Comment::COMMENT_TYPE_DESCRIPTION;
                $commentModel->target_id = $model->getPrimaryKey();

                $commentModel->uid = $this->getCurrentUid();
                $commentModel->visible_uids = '';
                $users = User::getRoleUsers(['admin','Backend-Manage','studio-manager','market','factory-template-member','rendering-member','factory-manger']);
                if($users){
                    foreach($users as $user){
                        $commentModel->visible_uids .= $user->id.',';
                    }
                }
                $commentModel->visible_uids = rtrim($commentModel->visible_uids,',');

            }else{
                $commentModel = Comment::findOne($commentId);
            }
            $commentModel->content = $comment;
            $commentModel->save();

            return $this->redirect(['process-list']);
        }else{
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }


    /**
     * 编辑开版属性
     * @return string|\yii\web\Response
     */
    public function actionUpdateAttributes($id)
    {
        $model = ProductTemplate::findOne($id);
        $attributesModel = $model->templateAttributes;

        if(empty($attributesModel)){
            $attributesModel = new ProductTemplateAttributes();
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $attributesModel->load(Yii::$app->request->post());
            $attributesModel->tpl_id=$model->getPrimaryKey();
            $attributesModel->save();
            return $this->redirect(['process-list']);
        }else{
            return $this->render('update-attributes', [
                'model' => $model,
                'attributesModel' => $attributesModel,
            ]);
        }
    }

    public function actionViewAttributes($id){
        $model = ProductTemplate::findOne($id);
        $attributesModel = $model->templateAttributes;

        if(empty($attributesModel)){
            $attributesModel = new ProductTemplateAttributes();
        }

        return $this->render('view-attributes', [
            'model' => $model,
            'attributesModel' => $attributesModel,
        ]);
    }

    /**
     * 衍生开版
     * @return string|\yii\web\Response
     */
    public function actionDerive($id)
    {
        $model = ProductTemplate::findOne($id);
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        }else{
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * 选品审核
     * @return array
     */
    public function actionApproval(){
        $posts = Yii::$app->request->post();
        if(!empty($posts['ids'])){
            $ids = explode(",",$posts['ids']);
            $model = new ProductTemplate();
            $model->approveTemplateStart($ids);
            return $this->json_output();
        }else{
            return $this->json_output(['status'=>0,'msg'=>'请选择要确认的项目']);
        }
    }

    /**
     * 工厂开版
     * @return array
     */
    public function actionFactoryStart(){
        $posts = Yii::$app->request->post();
        if(!empty($posts['ids'])){
            $ids = explode(",",$posts['ids']);
            $model = new ProductTemplate();
            $model->factoryStart($ids);
            return $this->json_output();
        }else{
            return $this->json_output(['status'=>0,'msg'=>'请选择要开版的项目']);
        }
    }

    /**
     * 开始电绘
     * @param $id
     * @return array
     */
    public function actionFactoryElectroplate($id){
        $model = ProductTemplate::findOne($id);
        $model->status = ProductTemplate::STATUS_FACTORY_STEP_ONE;
        $model->electroplate_at = time();
        $model->save();
        return $this->json_output();
    }

    /**
     * 开始银版
     * @param $id
     * @return array
     */
    public function actionFactorySilverTemplate($id){
        $model = ProductTemplate::findOne($id);
        $model->status = ProductTemplate::STATUS_FACTORY_STEP_TWO;
        $model->silver_at = time();
        $model->save();
        return $this->json_output();
    }

    /**
     * 开始压模
     * @param $id
     * @return array
     */
    public function actionFactoryMoulded($id){
        $model = ProductTemplate::findOne($id);
        $model->status = ProductTemplate::STATUS_FACTORY_STEP_THREE;
        $model->moulded_at = time();
        $model->save();
        return $this->json_output();
    }

    /**
     * 工厂开版结束
     * @param $id
     * @return array
     */
    public function actionFactoryEnd($id){
        $model = ProductTemplate::findOne($id);
        $model->status = ProductTemplate::STATUS_STUDIO_PENDING;
        $model->factory_end_at = time();
        $model->save();
        return $this->json_output();
    }

    /**
     * 开始渲染
     * @return array
     */
    public function actionStudioStart(){
        $posts = Yii::$app->request->post();
        if(!empty($posts['ids'])){
            $ids = explode(",",$posts['ids']);
            $model = new ProductTemplate();
            $model->studioStart($ids);
            return $this->json_output();
        }else{
            return $this->json_output(['status'=>0,'msg'=>'请选择要渲染的项目']);
        }
    }

    /**
     * 支付渲染费用
     * @return array
     */
    public function actionPaid(){
        $posts = Yii::$app->request->post();
        if(!empty($posts['ids'])){
            $ids = explode(",",$posts['ids']);
            $orderModel = new ProductTemplate();
            $orderModel->paid($ids);
            return $this->json_output();
        }else{
            return $this->json_output(['status'=>0,'msg'=>'请选择已支付的项目']);
        }
    }

    /**
     * 渲染结束
     * @param $id
     * @return array
     */
    public function actionStudioEnd($id){
        $model = ProductTemplate::findOne($id);
        if($model->type == '1'){
            $model->status = ProductTemplate::STATUS_ACCEPTED_PENDING;
        }else{
            $model->status = ProductTemplate::STATUS_CONFIRM_PENDING;
        }
        $model->studio_end_at = time();
        $model->studio_uid = $this->getCurrentUid();
        $model->save();
        return $this->json_output();
    }

    /**
     * 渲染结束
     * @param $id
     * @return array
     */
    public function actionStudioEndBatch(){
        $posts = Yii::$app->request->post();
        if(!empty($posts['ids'])){
            $ids = explode(",",$posts['ids']);
            $model = new ProductTemplate();
            $model->studioEndBatch($ids);
            return $this->json_output();
        }else{
            return $this->json_output(['status'=>0,'msg'=>'请选择要结束的项目']);
        }
    }

    /**
     * 工厂验收
     * @param $id
     * @return array
     */
    public function actionFactoryAccept($id){
        $model= ProductTemplate::findOne($id);
        if($model){
            $model->status = ProductTemplate::STATUS_ACCEPTED_PENDING;
            $model->factory_accepted_at = time();
            $model->factory_uid = $this->getCurrentUid();
            $model->save();
        }
        return $this->json_output();
    }

    /**
     * 流程结束
     * @param $id
     * @return array
     */
    public function actionFinished($id){
        $model= ProductTemplate::findOne($id);
        if($model){
            $model->status = ProductTemplate::STATUS_FINISHED;
            $model->finished_at = time();
            $model->finished_uid = $this->getCurrentUid();
            $model->save();
        }
        return $this->json_output();
    }

    /**
     * 流程结束
     * @param $id
     * @return array
     */
    public function actionFinishedBatch(){
        $posts = Yii::$app->request->post();
        if(!empty($posts['ids'])){
            $ids = explode(",",$posts['ids']);
            $model = new ProductTemplate();
            $model->flowFinished($ids);
            return $this->json_output();
        }else{
            return $this->json_output(['status'=>0,'msg'=>'请选择要结束的项目']);
        }
    }

    /**
     * 加急
     * @param $id
     * @return array
     */
    public function actionExpedited($id){
        $model= ProductTemplate::findOne($id);
        if($model){
            $model->expedited = ProductTemplate::EXPEDITED;
            $model->expedited_at = time();
            $model->save();
        }
        return $this->json_output();
    }

    /**
     * 加急确认
     * @return array
     */
    public function actionExpeditedConfirm(){
        $posts = Yii::$app->request->post();
        if(!empty($posts['ids'])){
            $ids = explode(",",$posts['ids']);
            $model = new ProductTemplate();
            $model->expeditedConfirm($ids);
            return $this->json_output();
        }else{
            return $this->json_output(['status'=>0,'msg'=>'请选择要确认加急的项目']);
        }
    }

    /**
     * 取消
     * @param $id
     * @return array
     */
    public function actionCancel($id){
        $model= ProductTemplate::findOne($id);
        if($model){
            $model->status = ProductTemplate::STATUS_CANCELLED;
            $model->save();
        }
        return $this->json_output();
    }


    /**
     * 请求返修
     * @param $id
     * @return array
     */
    public function actionRequestRepair($id){
        $model= ProductTemplate::findOne($id);
        if($model){
            $model->status = ProductTemplate::STATUS_REPAIR_PENDING;
            $model->save();
        }
        return $this->json_output();
    }

    /**
     * 开始返修
     * @param $id
     * @return array
     */
    public function actionStartRepair($id){
        $model= ProductTemplate::findOne($id);
        if($model){
            $model->status = ProductTemplate::STATUS_REPAIR;
            $model->save();
        }
        return $this->json_output();
    }

    /**
     * 修复完成
     * @param $id
     * @return array
     */
    public function actionFinishedRepair($id){
        $model= ProductTemplate::findOne($id);
        if($model){
            if($model->type == '1'){
                $model->status = ProductTemplate::STATUS_ACCEPTED_PENDING;
            }else{
                $model->status = ProductTemplate::STATUS_CONFIRM_PENDING;
            }
            $model->save();
        }
        return $this->json_output();
    }

    /**
     * 导出选品
     * @return array
     */
    public function actionExport(){
        $posts = Yii::$app->request->post();
        if(!empty($posts['ids'])){
            $model = new ProductTemplate();
            $results = $model->getExportData(explode(",",$posts['ids']));
            $header = $this->createExportHeader();
            $data = $this->formatExportData($results);
            $objExportData = new ExportData($header,$data);
            $objExportData->createExcel($header,$data);
            $path = 'download/product-template/'.date("Y-m-d").'/'.'template-'.date('Y-m-d-H-i-s').'.xls';
            $objExportData->saveFileTo($path);
            return $this->json_output(['data'=>['/'.$path]]);
        }else{
            return $this->json_output(['status'=>0,'msg'=>'请选择要导出的项目']);
        }
    }

    /**
     * 导出的头部
     * @return array
     */
    private function createExportHeader(){
        $header = [
            '编号',
            'SKU',
            '加急',
            '备注',
            '图片',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
        ];
        return $header;
    }

    /**
     * 格式化要导出的数据
     * @param $data
     * @return array
     */
    private function formatExportData($data){
        $output = [];
        $index = 0;
        foreach($data as $row){
            $output[$index][] = $row->ext_id;
            $output[$index][] = $row->sku;
            $output[$index][] = $row->expedited?'加急':'否';
            $comments = Comment::findAll(['target_id'=>$row->id,'type'=>'template']);
            $commentOutput = '无';
            if($comments){
                $commentOutput = '有';
            }
            $output[$index][] = $commentOutput;
            $files = $row->getFiles();
            foreach($files as $key => $file){
                $filePath = FileHelper::getThumbnailPath($file->file_path, '300x300');
                $filePath = str_replace(urlencode("#"),"#",$filePath);
                $output[$index][] = ['type'=>'image','value'=>$filePath];
            }
            if(count($output[$index])<15){
                $diff = 15-count($output[$index]);
                for($i=0;$i<$diff;$i++){
                    $output[$index][] = '';
                }
            }
            $index++;
        }
        return $output;
    }
}