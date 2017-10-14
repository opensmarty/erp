<?php
/**
 * Category.php 
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/5/17
 */

namespace app\models;


use yii\db\Query;
use renk\yiipal\helpers\FileHelper;
use yii\imagine\Image;
use yii\web\UploadedFile;

class File extends BaseModel{

    /**
     * @var UploadedFile[]
     */
    public $imageFiles;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'file_managed';
    }

    public function rules()
    {
        return [
            [['imageFiles'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg', 'maxFiles' => 8],
        ];
    }

    /**
     * 处理文件上传
     * @param $model
     * @param string $field
     * @param null $path
     * @param null $rename
     * @return array|bool
     */
    public function upload($model, $field='files', $path=null,$rename=null)
    {
        $this->imageFiles = UploadedFile::getInstances($model, $field);
        if($path == null){
            $path = 'uploads/'.date("Y-m-d").'/';
        }else{
            $path = 'uploads/'.$path.'/';
        }

        if(!is_dir($path)){
            mkdir($path,0755, true);
        }
        $fileIds = [];
        foreach ($this->imageFiles as $index=> $file) {
            if($rename == null){
                $filePath = $path . $file->name;
            }else{
                $filePath = $path . $rename . '.' . $file->extension;
            }
            $file->saveAs($filePath);
            $this->createThumbnail($filePath);
            $fileManger = new File();
            $fileManger->file_name = $file->name;
            $fileManger->file_path = $filePath;
            $fileManger->file_mime = $file->type;
            $fileManger->file_size = $file->size;
            $fileManger->save();
            $fileIds[] = $fileManger->getPrimaryKey();
        }
        return $fileIds;
    }

    /**
     * 创建缩略图
     * @param $filePath
     */
    private function createThumbnail($filePath){
        $thumbnails=[
            ['width'=>65,'height'=>65],
            ['width'=>100,'height'=>100],
            ['width'=>200,'height'=>200],
            ['width'=>300,'height'=>300],
        ];
        $basePath = dirname($filePath).'/thumbnail/';
        foreach($thumbnails as $thumbnail){
            $thumbnailPath = $basePath.$thumbnail['width'].'x'.$thumbnail['height'].'/';
            FileHelper::mkdir($thumbnailPath);
            Image::thumbnail($filePath,$thumbnail['width'],$thumbnail['height'])
                ->save($thumbnailPath.basename($filePath),['quality' => 90]);
        }

    }

    /**
     * 获取文件信息
     * @param array $ids
     * @return static[]
     */
    public static function getAllFiles($ids){
        return File::findAll(['in','id',$ids]);
    }

    /**
     * 获取单个文件信息
     * @param $id
     * @return null|static
     */
    public static function getFile($id){
        static $cache = [];
        if(isset($cache[$id])){
            return $cache[$id];
        }
        $cache[$id] = File::find()->where(['id'=>$id])->one();
        return $cache[$id];
    }
}