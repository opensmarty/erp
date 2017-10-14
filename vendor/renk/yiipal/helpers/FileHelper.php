<?php
/**
 * File.php
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/5/18
 */

namespace renk\yiipal\helpers;


class FileHelper extends \yii\helpers\FileHelper
{

    /**
     * 创建文件夹
     * @param $dir
     */
    public static function mkdir($dir){
        if(!is_dir($dir)){
            mkdir($dir,0755,true);
        }
    }

    /**
     * 获取缩略图地址
     * @param $filePath
     * @param $size
     * @return string
     */
    public static function getThumbnailPath($filePath,$size){
        if(empty($filePath)){
            return '/images/no_image.gif';
        }
        $path =  '/'.(dirname($filePath).'/thumbnail/'.$size.'/'.basename($filePath));
        return str_replace("#",urlencode("#"),$path);
    }

    /**
     * 获取缩略图标签
     * @param $filePath
     * @param $size
     * @return string
     */
    public static function getThumbnailImage($filePath,$size='300x300'){
        $filePath = self::getThumbnailPath($filePath,$size);
        return '<img src="'.$filePath.'" width="80"/>';
    }

    /**
     * 获取带链接的缩略图
     * @param $image
     * @param $id
     * @return string
     */
    public static function getThumbnailWithLink($image,$id){
        $file_path = isset($image->file_path)?$image->file_path:'';
        $file_path = str_replace("#",urlencode("#"),$file_path);
        $img =self::getThumbnailImage($file_path);
        $output = '<a href="/'.$file_path.'" data-lightbox="image-'.$id.'" data-title="">'.$img.'</a>';
        return $output;
    }
}