<?php
/**
 * ProductTemplate.php 
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/8/11
 */

namespace app\models\product;


use app\helpers\CommonHelper;
use app\models\BaseModel;
use app\models\Comment;
use app\models\File;
use app\models\User;
use renk\yiipal\helpers\Url;
use yii\web\UploadedFile;

class ProductTemplate extends BaseModel{

    const STATUS_PENDING	        = 'pending';
    const STATUS_CANCELLED	        = 'cancelled';
    const STATUS_APPROVAL	        = 'approval';
    const STATUS_FINISHED	        = 'finished';
    const STATUS_REPAIR_PENDING	    = 'repair_pending';
    const STATUS_REPAIR	            = 'repair';
    const STATUS_CONFIRM_PENDING	= 'confirm_pending';
    const STATUS_ACCEPTED_PENDING	= 'accepted_pending';
    const STATUS_ACCEPTED	        = 'accepted';
    const STATUS_FACTORY_PENDING	= 'factory_pending';
    const STATUS_FACTORY_START	    = 'factory_start';
    const STATUS_FACTORY_STEP_ONE	    = 'factory_step_1';
    const STATUS_FACTORY_STEP_TWO	    = 'factory_step_2';
    const STATUS_FACTORY_STEP_THREE	    = 'factory_step_3';
    const STATUS_FACTORY_END	    = 'factory_end';
    const STATUS_STUDIO_PENDING	    = 'studio_pending';
    const STATUS_STUDIO_START	    = 'studio_start';
    const STATUS_STUDIO_END	        = 'studio_end';


    const EXPEDITED = 1;
    const EXPEDITED_CONFIRM = 2;
    /**
     * @inheritdoc
     */
    public static function primaryKey()
    {
        return ['id'];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'product_template';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = [
            [$this->getTableSchema()->getColumnNames(),'safe'],
            [['sku','reason_note'], 'required'],
            [['based_sku'], 'required','when'=>function(){
                return $this->type>0;
            }],
            [['files'], 'required','when'=>function(){
                return empty($this->fids);
            }],
            [['files'], 'file', 'skipOnEmpty' => true, 'maxFiles' => 8],
            ['create_uid', 'default', 'value' => \Yii::$app->user->id],
        ];
        return $rules;
    }

    public function attributeLabels()
    {
        return [
            'sku' => '选品编号',
            'template_no' => '版号',
            'reason_note' => '选品理由',
            'country' => '国家',
            'chosen_uid' => '选品人',
            'type' => '类型',
            'category' => '版型分类',
            'is_original' => '是否原创',
            'electroplating_color' => '电镀颜色',
            'ext_id' => '开版编号',
            'expedited' => '加急',
            'status' => '状态',
            'based_sku' => '母版SKU',
            'create_uid' => '发起人',
            'created_at' => '发起时间',
            'approval_uid' => '审核人',
            'approval_at' => '审核时间',
            'factory_uid' => '工厂负责人',
            'factory_start_at' => '开版开始时间',
            'factory_end_at' => '开版完成时间',
            'studio_uid' => '渲染负责人',
            'studio_start_at' => '渲染开始时间',
            'studio_end_at' => '渲染完成时间',
            'finished_at' => '流程结束时间',
            'files' => '参考图片',
        ];
    }

    /**
     * 开版属性
     * @return \yii\db\ActiveQuery
     */
    public function getTemplateAttributes(){
        return $this->hasOne(ProductTemplateAttributes::className(),['tpl_id'=>'id']);
    }


    /**
     * 获取订单备注
     * @return $this
     */
    public function getComments(){
        $uid = \Yii::$app->user->id;
        return $this->hasMany(Comment::className(), ['target_id' => 'id'])
            ->where('type=:threshold1 AND FIND_IN_SET(:threshold2, visible_uids)', [':threshold1' => 'template',':threshold2' => $uid])
            ;
    }

    /**
     * 获取开版说明
     * @return $this
     */
    public function getDescriptionComment(){
        return $this->hasOne(Comment::className(), ['target_id' => 'id'])
            ->where('type=:threshold1 AND subject=:threshold2', [':threshold1' => 'template',':threshold2' => Comment::COMMENT_TYPE_DESCRIPTION])
            ;
    }

    /**
     * 列表也下拉按钮
     * @return array
     */
    public function buttons(){

        $buttons[] =             [
            'label'=>'查看',
            'url'=>Url::toRoute(['view','id'=>$this->id]),
            'icon'=>'',
            'attr' =>'',
        ];

        $buttons[] =             [
            'label'=>'查看属性',
            'url'=>Url::toRoute(['view-attributes','id'=>$this->id]),
            'icon'=>'',
            'attr' =>'',
        ];

        $buttons[] = [
            'label'=>'备注',
            'url'=>Url::toRoute(['/comment/create?target_id='.$this->id.'&type=template&comment_style=template&subject='.Comment::COMMENT_TYPE_OTHERS]),
            'icon'=>'glyphicon glyphicon-wrench',
            'attr' =>['class'=>'btn-order-expedite ajax-modal'],
        ];

        if($this->status == self::STATUS_CANCELLED){
            return $buttons;
        }

        if($this->status == self::STATUS_PENDING || \Yii::$app->user->can('/permission/template-edit')){
            $buttons[] = [
                'label'=>'编辑',
                'url'=>Url::toRoute(['update','id'=>$this->id]),
                'icon'=>'',
                'attr' =>'',
            ];
        }

        $buttons[] = [
            'label'=>'属性',
            'url'=>Url::toRoute(['update-attributes','id'=>$this->id]),
            'icon'=>'',
            'attr' =>'',
        ];

        $buttons[] = [
            'label'=>'删除',
            'url'=>Url::toRoute(['delete','id'=>$this->id]),
            'icon'=>'',
            'attr' =>['class'=>'confirm'],
        ];

        $buttons[] = [
            'label'=>'取消',
            'url'=>Url::toRoute(['cancel','id'=>$this->id]),
            'icon'=>'',
            'attr' =>['class'=>'btn-order-cancel label-danger ajax-with-comment', 'data'=>['commentUrl'=>'/comment/create?target_id='.$this->id.'&type=order&comment_style=template&subject='.Comment::COMMENT_TYPE_CANCEL]],
        ];

        if($this->status == self::STATUS_FACTORY_START){
            $buttons[] = [
                'label'=>'开始电绘',
                'url'=>Url::toRoute(['factory-electroplate','id'=>$this->id]),
                'icon'=>'',
                'attr' =>['class'=>'ajax'],
            ];
        }

        if($this->status == self::STATUS_FACTORY_STEP_ONE){
            $buttons[] = [
                'label'=>'开始银版',
                'url'=>Url::toRoute(['factory-silver-template','id'=>$this->id]),
                'icon'=>'',
                'attr' =>['class'=>'ajax'],
            ];
        }

        if($this->status == self::STATUS_FACTORY_STEP_TWO){
            $buttons[] = [
                'label'=>'开始压模',
                'url'=>Url::toRoute(['factory-moulded','id'=>$this->id]),
                'icon'=>'',
                'attr' =>['class'=>'ajax'],
            ];
        }


        if($this->status == self::STATUS_FACTORY_STEP_THREE){
            $buttons[] = [
                'label'=>'开版完成',
                'url'=>Url::toRoute(['factory-end','id'=>$this->id]),
                'icon'=>'',
                'attr' =>['class'=>'ajax'],
            ];
        }

        if($this->status == self::STATUS_STUDIO_START) {
            $buttons[] = [
                'label' => '渲染完成',
                'url' => Url::toRoute(['studio-end', 'id' => $this->id]),
                'icon' => '',
                'attr' => ['class' => 'ajax'],
            ];
        }

        if($this->status == self::STATUS_CONFIRM_PENDING) {
            $buttons[] = [
                'label' => '检查通过',
                'url' => Url::toRoute(['factory-accept', 'id' => $this->id]),
                'icon' => '',
                'attr' => ['class' => 'ajax'],
            ];
            $buttons[] = [
                'label' => '需要返修',
                'url' => Url::toRoute(['request-repair', 'id' => $this->id]),
                'icon' => '',
                'attr' =>['class'=>'btn-order-expedite ajax-with-comment', 'data'=>['commentUrl'=>'/comment/create?target_id='.$this->id.'&type=template&comment_style=template&subject='.Comment::COMMENT_TYPE_DESCRIPTION]],
            ];
        }



        if($this->status == self::STATUS_ACCEPTED_PENDING) {
            $buttons[] = [
                'label'=>'流程结束',
                'url'=>Url::toRoute(['finished','id'=>$this->id]),
                'icon'=>'',
                'attr' =>['class'=>'ajax'],
            ];
            if($this->type == 1){
                $buttons[] = [
                    'label' => '需要返修',
                    'url' => Url::toRoute(['request-repair', 'id' => $this->id]),
                    'icon' => '',
                    'attr' =>['class'=>'btn-order-expedite ajax-with-comment', 'data'=>['commentUrl'=>'/comment/create?target_id='.$this->id.'&type=template&comment_style=template&subject='.Comment::COMMENT_TYPE_DESCRIPTION]],
                ];
            }
        }

        if($this->expedited == 0 && $this->status != self::STATUS_FINISHED) {
            $buttons[] = [
                'label' => '加急',
                'url' => Url::toRoute(['expedited', 'id' => $this->id]),
                'icon' => '',
                'attr' =>['class'=>'btn-order-expedite ajax-with-comment', 'data'=>['commentUrl'=>'/comment/create?target_id='.$this->id.'&type=template&comment_style=template&subject='.Comment::COMMENT_TYPE_EXPEDITE]],
            ];
        }

        if($this->status == self::STATUS_REPAIR_PENDING) {
            $buttons[] = [
                'label' => '开始返修',
                'url' => Url::toRoute(['start-repair', 'id' => $this->id]),
                'icon' => '',
                'attr' => ['class' => 'ajax'],
            ];
        }

        if($this->status == self::STATUS_REPAIR) {
            $buttons[] = [
                'label' => '返修完成',
                'url' => Url::toRoute(['finished-repair', 'id' => $this->id]),
                'icon' => '',
                'attr' => ['class' => 'ajax'],
            ];
        }

        return $buttons;
    }

    public function getChosenUser(){
        return $this->hasOne(User::className(),['id'=>'chosen_uid']);
    }

    public function getCreateUser(){
        return $this->hasOne(User::className(),['id'=>'create_uid']);
    }

    public function getApprovalUser(){
        return $this->hasOne(User::className(),['id'=>'approval_uid']);
    }

    public function getFactoryUser(){
        return $this->hasOne(User::className(),['id'=>'factory_uid']);
    }

    public function getStudioUser(){
        return $this->hasOne(User::className(),['id'=>'studio_uid']);
    }

    public function getFinishedUser(){
        return $this->hasOne(User::className(),['id'=>'finished_uid']);
    }

    /**
     * 获取参考主图
     * @return bool|null|static
     */
    public function getMasterImage(){
        $fids = explode(",",$this->fids);
        if($fids){
            return File::getFile($fids[0]);
        }else{
            return false;
        }
    }

    /**
     * 保存
     * @param bool $runValidation
     * @param null $attributeNames
     */
    public function save($runValidation = true, $attributeNames = null){
        if($this->files && UploadedFile::getInstances($this,'files')){
            $fileModel = new File();
            $path = 'products/'.$this->sku;
            $fileIds = $fileModel->upload($this,'files',$path);
            if(empty($this->fids)){
                $this->fids = $fileIds?join(",", $fileIds):'';
            }else{
                $fids = $fileIds?join(",", $fileIds):'';
                if(!empty($fids)){
                    $this->fids .= ','.$fids;
                }
            }

        }
        $this->files = null;
        return parent::save($runValidation,$attributeNames);
    }

    /**
     * 支付渲染费用
     * @param $ids
     */
    public function paid($ids){
        //ProductTemplate::updateAll(['payment_status' => 'paid','paid_uid'=>$this->getCurrentUid(),'paid_at'=>time()],['in','id',$ids]);
        ProductTemplate::updateAll(['payment_status' => 'paid','paid_uid'=>$this->getCurrentUid(),'paid_at'=>time()],['and',['in','id',$ids],['>','render_price','0']]);
    }


    /**
     * 确认发起
     * @param $ids
     */
    public function approveTemplateStart($ids){
        foreach($ids as $id){
            $model = ProductTemplate::find()->where(['status'=>self::STATUS_PENDING,'id'=>$id])->one();
            if(empty($model))continue;
            if($model->type ==1){
                $model->status = self::STATUS_STUDIO_PENDING;
            }else{
                $model->status = self::STATUS_FACTORY_PENDING;
            }
            $model->approval_at = time();
            $model->approval_uid = $this->getCurrentUid();
            $model->save();
        }
    }


    /**
     * 格式化查询参数
     * @param $query
     * @param null $params
     */
    protected function formatQueryParams(&$query, $params=[],$defaultParams=false){

        if(empty($params) && $defaultParams==false){
            return false;
        }
        $modelName = basename(str_replace('\\','/',self::className()));
        if(!isset($params[$modelName]) || !is_array($params[$modelName])){
            $params = [];
        }else{
            $params = $this->formatSearchParams($params[$modelName]);
        }
        foreach($params as $item){
            $key = key($item);

            if($key === 'chosen_uid'){
                $uid = 0;
                $user = User::find()->where(['nick_name'=>$item[$key]])->one();
                $uid = $user?$user->id:0;
                $query->andFilterWhere([$key=>$uid]);
                continue;
            }

            if(preg_match('/.*_at$/',$key)){
                $date = explode("/", $item[$key]);
                if(count($date)==1){
                    $query->andFilterWhere($item);
                }else{
                    $query->andFilterWhere(['>',$key,strtotime($date[0].' 00:00:00')]);
                    $query->andFilterWhere(['<',$key,strtotime($date[1]." 23:59:59")]);
                }

            }
            elseif($key === 'render_price'){
                if($item[$key] == '1'){
                    $query->andFilterWhere(['>','render_price',0]);
                }elseif($item[$key] == '-1'){
                    $query->andFilterWhere(['<','render_price',1]);
                }
            }
            else{
                $query->andFilterWhere($item);
            }

        }
        if($defaultParams){
            foreach($defaultParams as $defaultParam){
                $query->andFilterWhere($defaultParam);
            }
        }
    }

    /**
     * 工厂开版
     * @param $ids
     */
    public function factoryStart($ids){
        self::updateAll(['status' => self::STATUS_FACTORY_START,'factory_start_at'=>time(),'factory_uid'=>$this->getCurrentUid()],['and',['in','id',$ids],['status'=>self::STATUS_FACTORY_PENDING]]);
    }

    /**
     * 工作室渲染
     * @param $ids
     */
    public function studioStart($ids){
        self::updateAll(['status' => self::STATUS_STUDIO_START,'studio_start_at'=>time(),'studio_uid'=>$this->getCurrentUid()],['and',['in','id',$ids],['status'=>self::STATUS_STUDIO_PENDING]]);
    }

    /**
     * 工作室渲染
     * @param $ids
     */
    public function studioEndBatch($ids){
        foreach($ids as $id){
            $model = ProductTemplate::find()->where(['id'=>$id,'status'=>self::STATUS_STUDIO_START])->one();
            if(empty($model)) continue;
            if($model->type == '1'){
                $model->status = ProductTemplate::STATUS_ACCEPTED_PENDING;
            }else{
                $model->status = ProductTemplate::STATUS_CONFIRM_PENDING;
            }
            $model->studio_end_at = time();
            $model->studio_uid = $this->getCurrentUid();
            $model->save();
        }
    }

    /**
     * 加急确认
     * @param $ids
     */
    public function expeditedConfirm($ids){
        self::updateAll(['expedited' => self::EXPEDITED_CONFIRM,'expedited_confirm_at'=>time()],['and',['in','id',$ids],['expedited'=>self::EXPEDITED]]);
    }

    /**
     * 获取导出数据
     * @param $ids
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getExportData($ids){
        $results = ProductTemplate::find()->where(['in','id',$ids])->all();
        return $results;
    }

    public function flowFinished($ids){
        self::updateAll(['status' => self::STATUS_FINISHED,'finished_at'=>time(),'finished_uid'=>$this->getCurrentUid()],['and',['in','id',$ids],['status'=>self::STATUS_ACCEPTED_PENDING]]);
    }

    /**
     * 获取渲染费用的统计信息
     * @param bool $dateRange
     * @return array
     */
    public function getRenderCostInfo($dateRange=false){
        $output = [];
        $query = self::find()->where(['status'=>'finished']);
        if($dateRange){
            $date = CommonHelper::splitDateRange($dateRange);
            $query->andWhere(['>','finished_at',$date['start']]);
            $query->andWhere(['<=','finished_at',$date['end']]);
        }

        $totalNumber = $query->count();
        $totalPrice = $query->sum('render_price');
        $paid = $query->andWhere(['payment_status'=>'paid'])->sum('render_price');
        $output['total_number'] = $totalNumber;
        $output['total_price'] = round($totalPrice,2);
        $output['paid'] = round($paid,2);
        return $output;
    }
}