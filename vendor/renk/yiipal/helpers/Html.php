<?php

namespace renk\yiipal\helpers;
use Yii;
use yii\helpers\BaseHtml;


class Html extends BaseHtml
{
    /**
     * 生成链接
     * @param $text
     * @param null $url
     * @param array $options
     * @return string
     */
    public static function authLink($text, $url = null, $options = []){
        if (Yii::$app->user->can(Url::to($url))) {
            return self::a($text, $url,$options);
        }else{
            return '';
        }
    }

    /**
     * 生成带符号的链接
     * @param $text
     * @param null $url
     * @param array $options
     * @return string
     */
    public static function authIconLink($text, $url = null, $options = [],$icon='glyphicon glyphicon-pencil'){
        if (Yii::$app->user->can(Url::to($url))) {
            return self::a('<i class="'.$icon.'"></i>'.$text, $url,$options);
        }else{
            return '';
        }
    }

    /**
     * 生成带有备注的链接
     * @param $text
     * @param $commentUrl
     * @param null $url
     * @param array $options
     * @param string $icon
     * @return string
     */
    public static function authLinkWithComment($text, $commentUrl, $url, $options = [],$icon='glyphicon glyphicon-pencil'){
        if(isset($options['class'])){
            $options['class'] .= ' ajax-with-comment';
        }else{
            $options['class'] = ' ajax-with-comment';
        }

        if($icon){
            $prefixIcon = '<i class="'.$icon.'"></i>';
        }else{
            $prefixIcon = '';
        }

        $options['data'] = json_encode(['commentUrl'=>$commentUrl]);
        $urlInfo =parse_url($url);
        if (Yii::$app->user->can(Url::to($urlInfo['path']))) {
            return self::a($prefixIcon.$text, $url,$options);
        }else{
            return '';
        }
    }

    /**
     * 生成提交按钮
     * @param $text
     * @param null $url
     * @param array $options
     * @return string
     */
    public static function authSubmitButton($text, $url = null, $options = [],$extraData=[]){
        if (Yii::$app->user->can(Url::to($url))) {
            $formClass = isset($options['form-class'])?$options['form-class']:'';
            $output = Html::beginForm($url,'post',['class'=>'inline-block '.$formClass]);
            $output .= Html::hiddenInput('ids','',['id'=>'ids']);
            foreach($extraData as $name=>$value){
                $output .= Html::hiddenInput($name,$value);
            }
            $output .= Html::submitButton($text, $options);
            $output .= Html::endForm();
            return $output;
        }else{
            return '';
        }
    }
}
