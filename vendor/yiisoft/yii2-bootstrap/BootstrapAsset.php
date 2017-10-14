<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\bootstrap;

use yii\web\AssetBundle;

/**
 * Asset bundle for the Twitter bootstrap css files.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class BootstrapAsset extends AssetBundle
{
    public $sourcePath = null;//'@bower/bootstrap/dist';
    private $cdnPath = '//cdn.bootcss.com/bootstrap/3.3.6/';
    public $css = [
//        'css/bootstrap.css',
    ];
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->css[] = $this->cdnPath. 'css/bootstrap.min.css';
    }
}
