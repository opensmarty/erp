<?php
namespace app\assets;

use yii\web\AssetBundle;
use Yii;

/**
 * CDN资源
 * Class AppPluginsAsset
 * @package app\assets
 */
class AppPluginsAsset extends AssetBundle
{
    public $sourcePath = null;
    public $css = [
        '//cdn.bootcss.com/bootstrap-daterangepicker/2.1.21/daterangepicker.min.css',
        '//cdn.bootcss.com/lightbox2/2.8.2/css/lightbox.min.css',
        '//cdn.bootcss.com/bootstrap-treeview/1.2.0/bootstrap-treeview.min.css',
        '//cdn.bootcss.com/jstree/3.3.1/themes/default/style.min.css',
        '//cdn.bootcss.com/x-editable/1.5.1/bootstrap3-editable/css/bootstrap-editable.css',
//        '//cdn.bootcss.com/morris.js/0.5.1/morris.css',
    ];
    public $js = [
        '//cdn.bootcss.com/lightbox2/2.8.2/js/lightbox.min.js',
//        '//cdn.bootcss.com/ckeditor/4.5.9/ckeditor.js',
        'http://54.64.154.102/js/plugins/ckeditor/ckeditor.js',
        'http://54.64.154.102/js/plugins/ckeditor/config.js',
        '//cdn.bootcss.com/bootstrap-treeview/1.2.0/bootstrap-treeview.min.js',
        '//cdn.bootcss.com/bootstrap-daterangepicker/2.1.21/moment.min.js',
        '//cdn.bootcss.com/bootstrap-daterangepicker/2.1.21/daterangepicker.min.js',
        '//cdn.bootcss.com/jquery.fileDownload/1.4.2/jquery.fileDownload.min.js',
        '//cdn.bootcss.com/jquery.form/3.51/jquery.form.min.js',
        '//cdn.bootcss.com/js-cookie/2.1.2/js.cookie.min.js',
        '//cdn.bootcss.com/jquery.countdown/2.1.0/jquery.countdown.min.js',
        '//cdn.bootcss.com/bootbox.js/4.4.0/bootbox.min.js',
        '//cdn.bootcss.com/jstree/3.3.1/jstree.min.js',
        '//cdn.bootcss.com/x-editable/1.5.1/bootstrap3-editable/js/bootstrap-editable.js',
        '//cdn.bootcss.com/raphael/2.2.1/raphael.min.js',
        '//cdn.bootcss.com/morris.js/0.5.1/morris.min.js',
        '//cdn.bootcss.com/echarts/3.2.2/echarts.min.js',
    ];
}
