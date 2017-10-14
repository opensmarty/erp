<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace renk\yiipal\helpers;
use yii\helpers\BaseUrl;

/**
 * Url provides a set of static methods for managing URLs.
 *
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @since 2.0
 */
class Url extends BaseUrl
{
    public function getBaseUrl($url){
        $url_parts = parse_url($url);
        $constructed_url = $url_parts['scheme'] . '://' . $url_parts['host'] . (isset($url_parts['path'])?$url_parts['path']:'');
        return $constructed_url;
    }

    public static function arg($index = NULL, $path = NULL){
        $args = [];
        if (isset($path)) {
            $param = $path;
        }else{
            $param = \Yii::$app->getRequest()->getPathInfo();
        }
        if(!empty($param)){
            $args = explode('/', $param);
        }
        if (!isset($index)) {
            return $args;
        }
        if (isset($args[$index])) {
            return $args[$index];
        }
    }
}
