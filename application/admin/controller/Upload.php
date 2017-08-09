<?php
/**
 * Created by PhpStorm.
 * User: 虚空之翼
 * Date: 16/7/21
 * Time: 下午2:23
 */

namespace app\admin\controller;

use app\admin\model\Picture;
use app\admin\model\File as FileModel;

class Upload
{
    public function index() {

    }
    public function file(){
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('file_data');
        // 移动到框架应用根目录/public/uploads/ 目录下
        $info = $file->move(ROOT_PATH . 'public/uploads/file');
        if($info){
            $path = '/uploads/file/'.$info->getSaveName();
            $data = [
                'code' => 1,
                'msg' => '上传成功',
                'data' => $path
            ];
            return json_encode($data);
        } else {
            // 上传失败获取错误信息
            $data = [
                'code' => 0,
                'msg' => $file->getError()
            ];
            return json_encode($data);
        }
    }

    public function picture(){
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('file_data');
        // 移动到框架应用根目录/public/uploads/ 目录下
        $info = $file->move(ROOT_PATH . 'public/uploads/picture');
        if($info){
            $path = '/uploads/picture/'.$info->getSaveName();
            $data = [
                'code' => 1,
                'msg' => '上传成功',
                'data' => $path
            ];
            return json_encode($data);
        } else {
            // 上传失败获取错误信息
            $data = [
                'code' => 0,
                'msg' => $file->getError()
            ];
            return json_encode($data);
        }
    }

    public function video(){
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('file_data');
        // 移动到框架应用根目录/public/uploads/ 目录下
        $info = $file->move(ROOT_PATH . 'public/uploads/video');
        if($info){
            $path = '/uploads/video/'.$info->getSaveName();
            $data = [
                'code' => 1,
                'msg' => '上传成功',
                'data' => $path
            ];
            return json_encode($data);
        } else {
            // 上传失败获取错误信息
            $data = [
                'code' => 0,
                'msg' => $file->getError()
            ];
            return json_encode($data);
        }
    }

    public function del() {
        $return  = array('code' => 1, 'message' => '上传成功', 'data' => '');
        return json_encode($return);
    }
}