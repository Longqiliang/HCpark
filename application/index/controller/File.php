<?php

namespace app\index\controller;

use app\index\model\File as FileModel;
use app\index\model\Picture;
use think\Config;

class File extends Base
{
    /* 文件上传 */
    public function upload(){
        $return  = array('status' => 1, 'info' => '上传成功', 'data' => '');
        /* 调用文件上传组件上传文件 */
        $File = new FileModel();
        $file_driver = Config::get('upload_drive');
        $info = $File->upload(
            $_FILES,
            Config::get('download_upload'),
            Config::get('upload_drive'),
            Config::get("upload_{$file_driver}_config")
        );
        /* 记录附件信息 */
        if($info){
            $return['data'] = $info['download'];
            $return['info'] = "上传成功";
            $return['path'] = '/uploads/download/'.$info['download']['savepath'].$info['download']['savename'];
        } else {
            $return['status'] = 0;
            $return['info'] = $File->getError();
        }

        /* 返回JSON数据 */
        return json_encode($return);
    }

    /* 下载文件 */
    public function download($id = null){
        if(empty($id) || !is_numeric($id)){
            return $this->error('参数错误！');
        }

//        $logic = D('Download', 'Logic');
//        if(!$logic->download($id)){
//            return $this->error($logic->getError());
//        }

    }

    /**
     * 上传图片
     */
    public function uploadPicture(){
        /* 调用文件上传组件上传文件 */


        $Picture = new Picture();
        $pic_driver = Config::get('upload_drive');

        $info = $Picture->upload(
            $_FILES,
            Config::get('download_upload'),
            Config::get('upload_drive'),
            Config::get("upload_{$pic_driver}_config")
        );

        /* 记录图片信息 */
        /* 记录附件信息 */
        if($info){
            $return['data'] = $info['picture'];
            $return['code'] = 1;
        } else {
            $return['code'] = 0;
            $return['info'] = $Picture->getError();
        }

        return json_encode($return);
    }

    /**
     * 保存64位编码图片,base64位不用任何处理
     */
    function saveBase64Image(){
        $base64 = $_POST['imgdata'];
        if($base64){
            $base64_image = str_replace(' ', '+', $base64);
            //post的数据里面，加号会被替换为空格，需要重新替换回来，如果不是post的数据，则注释掉这一行
            if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image, $result)){
                //匹配成功
                if($result[2] == 'jpeg'){
                    //如果是jpeg替换成jpg
                    $image_name = uniqid().'.jpg';
                }else{
                    //生成一个随机名
                    $image_name = uniqid().'.'.$result[2];
                }
                //判断当日的文件夹是否存在,不存在就创建
                $root_path = Config::get('download_upload');
                $pic_path = $root_path['rootPath'].date("Y-m-d").'/';
                $save_name = "/uploads/download/".date("Y-m-d").'/'.$image_name;
                !file_exists($pic_path) && mkdir($pic_path, 0777);
                //保存图片的路径
                $image_file = $pic_path.$image_name;
                //服务器文件存储路径
                if (file_put_contents($image_file, base64_decode(str_replace($result[1], '', $base64_image)))){
                    $data['code']=1;
                    $data['imageName']=$image_name;
                    $data['url']=$save_name;
                    $data['msg']='保存成功！';
                }else{
                    $data['code']=0;
                    $data['msg']='图片保存失败！';
                }
            }else{
                $data['code']=0;
                $data['msg']='图片base64码格式错误！';
            }
            //返回数据
            return $data;
        }else{
            return $this ->error('错误');
        }
    }
}
