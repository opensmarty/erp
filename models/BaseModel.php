<?php

namespace app\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;
use yii\helpers\Url;
use yii\web\UploadedFile;


class BaseModel extends ActiveRecord
{
    public $files = [];

    //忽略上传文件
    public $ignoreFiles = false;
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

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
        return strtolower(basename(str_replace('\\','/',self::className())));
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


    /**
     * Creates data provider instance with search query applied
     * @param $class
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params=null, $class=null,$defaultParams=false)
    {
        if($class == null){
            $class = self::className();
        }
        $query = $class::find();
        // add conditions that should always apply here
        $this->load($params);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> ['defaultOrder' => ['created_at'=>SORT_DESC]]
        ]);

        $this->formatQueryParams($query, $params,$defaultParams);

        return $dataProvider;
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
            if(preg_match('/.*_at$/',$key)){
                $date = explode("/", $item[$key]);
                if(count($date)==1){
                    $query->andFilterWhere($item);
                }else{
                    $query->andFilterWhere(['>',$key,strtotime($date[0].' 00:00:00')]);
                    $query->andFilterWhere(['<',$key,strtotime($date[1]." 23:59:59")]);
                }

            }else{
                $query->andWhere($item);
            }
        }
        if($defaultParams){
            foreach($defaultParams as $defaultParam){
                $query->andWhere($defaultParam);
            }
        }
    }


    /**
     * 格式化查询参数
     * @param $params
     * @return array
     */
    protected function formatSearchParams($params){
        $output = [];
        foreach($params as $field=>$item){
            $item = trim($item);
            if($item == ''){
                continue;
            }
            $output[] = [$field=>$item];
        }
        return $output;
    }

    /**
     * 列表页默认按钮组
     * @return array
     */
    public function buttons(){
        return [
            [
                'label'=>'查看',
                'url'=>'view?id='.$this->id,
                'icon'=>'',
                'attr' =>'',
            ],
            [
                'label'=>'编辑',
                'url'=>Url::toRoute(['update','id'=>$this->id]),
                'icon'=>'',
                'attr' =>'',
            ],
            [
                'label'=>'删除',
                'url'=>Url::toRoute(['delete','id'=>$this->id]),
                'icon'=>'',
                'attr' =>['class'=>'confirm'],
            ],
        ];
    }


    /**
     * 保存
     * @param bool $runValidation
     * @param null $attributeNames
     */
    public function save($runValidation = true, $attributeNames = null){
        if($this->files && UploadedFile::getInstances($this,'files')){
            $fileModel = new File();
            $fileIds = $fileModel->upload($this);
            $this->fids = $fileIds?join(",", $fileIds):'';
        }
        $this->files = null;
        return parent::save();
    }

    /**
     * 获取关联的文件
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getFiles(){
        $fids = $this->fids;
        if(!empty($fids)){
            $fids = explode(",", $fids);
            $files = File::find()->where(['in','id',$fids])->all();
            $this->files = $files;
        }
        return $this->files;
    }

    /**
     * 获取产品主图
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
     * 获取用户的ID
     * @return int|string
     */
    protected function getCurrentUid(){
        return Yii::$app->user->id;
    }
}
