<?php

namespace app\models;

use app\models\user\AuthAssignment;
use app\models\user\AuthItem;
use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;
use yii\db\Query;
use yii\web\IdentityInterface;
use mdm\admin\components\Configs;

/**
 * User model
 *
 * @property integer $id
 * @property string $username
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email
 * @property string $auth_key
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $password write-only password
 *
 * @property UserProfile $profile
 */
class User extends BaseModel implements IdentityInterface
{
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 10;

    public $new_password = '';
    public $item_name = '';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return Configs::instance()->userTable;
    }

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
    public function rules()
    {
        return [
            [['new_password','nick_name','email','new_password','username','item_name','websites'],'safe'],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE]],
        ];
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
        $query = $class::find()->with('roles')
                ->leftjoin("auth_assignment","auth_assignment.user_id=user.id");
        // add conditions that should always apply here
        $this->load($params);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> ['defaultOrder' => ['created_at'=>SORT_ASC]]
        ]);

        $this->formatQueryParams($query, $params,$defaultParams);

        return $dataProvider;
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

    public function getAssignment()
    {
        return $this->hasMany(AuthAssignment::className(), ['user_id' => 'id']);
    }

    public function getRoles(){
        return $this->hasMany(AuthItem::className(), ['name' => 'item_name'])
            ->where('type = :threshold', [':threshold' => '1'])
            ->via('assignment');
    }


//    public function getRoles()
//    {
//        return $this->hasMany(AuthItem::className(), ['name' => 'item_name','type'=>'1'])
//            ->viaTable('auth_assignment', ['user_id' => 'id']);
//    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::find()->where(['id' => $id, 'status' => self::STATUS_ACTIVE])->one();
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::find()->where(['username' => $username, 'status' => self::STATUS_ACTIVE])->one();
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return boolean
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        $parts = explode('_', $token);
        $timestamp = (int) end($parts);
        return $timestamp + $expire >= time();
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    public function attributeLabels()
    {
        return [
            'username' => '用户名',
            'nick_name' => '姓名',
            'new_password' => '密码',
            'email' => '邮箱',
            'websites' => '网站',
            'created_at' => Yii::t("app","created_at"),
            'status' => Yii::t("app","status"),
        ];
    }

    /**
     * 获取角色及用户
     * @return array
     */
    public function getRolesWithUsers(){
        $query = new Query();
        $results = $query->select(['i.description as role_label', 'i.name as role_name','i.type','u.id as user_id','u.username'])
            ->from('auth_assignment a')
            ->innerJoin('auth_item i','a.item_name=i.name and i.type=1')
            ->innerJoin('user u','u.id=a.user_id')
            ->where(['<>','a.item_name','none'])
            ->orderBy("i.created_at ASC")
            ->all();
        return $results;
    }

    /**
     * 获取指定角色的用户
     * @param $role_name
     * @return array
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public static function getRoleUsers($role_name=[],$options = true)
    {
        return static::find()
            ->join('LEFT JOIN','auth_assignment','auth_assignment.user_id = id')
            ->where(['in','auth_assignment.item_name',$role_name])
            ->andWhere(['status'=>self::STATUS_ACTIVE])
            ->all();
    }
}
