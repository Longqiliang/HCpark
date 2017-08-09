<?php

namespace app\admin\model;

use org\ImageImagick;
use think\Model;

/**
 * 文件模型
 * 负责文件的下载和上传
 */

class File extends Model
{
    protected $insert = [ 'create_time' => NOW_TIME ];

    public function upload($files, $setting=null) {
        // 获取表单上传文件
        if (empty($files)) {
            return false;
        }
        // 逐个上传文件
        $updateFiles = [];
        $config = $setting ? array_merge(config('file'), $setting) : config('file');
        $savePath = ROOT_PATH.$config['path'];
        $filesData = [];
        if ($this->arrayLevel($files)) {
            $filesData = $files;
        } else {
            $filesData[] = $files;
        }
        foreach ($filesData as $key=>$file) {
            // 移动到框架应用根目录/public/uploads/ 目录下
            $info = $file->validate(['size'=>$config['size'],'ext'=>$config['ext']])->move($savePath);
            // 检查文件是否存在
            $md5 = md5_file($info->getRealPath());
            $sha1 = sha1_file($info->getRealPath());
            $oldFile = $this->where('md5', $md5)->find();
            if ($oldFile) {
                unlink($info->getRealPath());
                $updateFiles[$key] = $oldFile;
            } else {
                /* 记录文件信息 */
                $data = array(
                    'name' => $info->getInfo()['name'],
                    'savename' => $info->getFilename(),
                    'path' => config('file.path').$info->getSaveName(),
                    'ext' => $info->getExtension(),
                    'type' => $info->getInfo()['type'],
                    'size' => $info->getSize(),
                    'md5' => $md5,
                    'sha1' => $sha1,
                    'location' => 1,
                );

                $updateFiles[$key] = $this->create($data);
            }
        }
        return $updateFiles;
    }

    public function uploadOne($file, $setting=null){
        $info = $this->upload($file, $setting);
        return $info ? $info[0] : $info;
    }

    /**
     * 检查是否上传多个文件
     * @param $values
     * @return bool
     */
    protected function arrayLevel($values) {
        if (is_object($values[0])) {
            return true;
        }
        return false;
    }

    /**
     * 清除数据库存在但本地不存在的数据
     * @param $data
     */
    public function removeTrash($data){
        $this->destroy($data['id']);
    }

    /**
     * 生成缩略图
     * @param $path
     * @param $thumbnailPath
     */
    protected function crop($path, $thumbnailPath) {
        $images = new ImageImagick('.'.$path);

        $targetHeight = 400;
        $targetWidth = 600;
        $height = $images->height();
        $width = $images->width();
        $heightMultiple = $height / $targetHeight;
        $widthMultiple = $width / $targetWidth;

        // 根据不同的比例进行切图
        if($heightMultiple > $widthMultiple) {
            $saveWidth = $width;
            $saveHeight = $targetHeight / $targetWidth * $width;
            $images->crop($saveWidth, $saveHeight, 0, ($height-$saveHeight)/2)->save('.'.$thumbnailPath);
        } elseif($heightMultiple < $widthMultiple) {
            $saveWidth = $targetWidth / $targetHeight * $height;
            $saveHeight = $height;
            $images->crop($saveWidth, $saveHeight, ($width-$saveWidth)/2, 0)->save('.'.$thumbnailPath);
        } else {
            $images->crop($width, $height)->save('.'.$thumbnailPath);
        }
    }

    /**
     * Base编码转图片文件
     * @param $base64
     * @param string $ext
     * @return bool|string
     */
    public static function base64ToImage($base64, $ext='png') {
        $images = base64_decode($base64);

        $path = './uploads/header/'.date('Y-m-d');
        if(!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        $name = 'header_'.NOW_TIME.'.'.$ext;

        $result = file_put_contents($path.'/'.$name, $images);
        if($result) {
            return ltrim($path.'/'.$name, ".");
        } else {
            return false;
        }
    }

}
