<?php
/**
 * Cron.php 
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/6/24
 */

namespace app\models;


use yii\data\ActiveDataProvider;

class Cron extends BaseModel{


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
        $query = $class::find()->with('user');
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
     * 获取任务创建人信息
     * @return \yii\db\ActiveQuery
     */
    public function getUser(){
        return $this->hasOne(User::className(), ['id' => 'uid']);
    }

    public function attributeLabels()
    {
        return [
            'name' => '任务名称',
            'url' => '任务模块',
            'params' => '任务参数',
            'enabled' => '任务状态',
            'uid' => '任务创建人',
            'last_run_time' => '最后一次运行时间',
        ];
    }
}