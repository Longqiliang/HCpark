<?php

namespace app\index\model;
use think\Controller;
use think\Model;
use org\Upload;

/**
 * 文件模型
 * 负责文件的下载和上传
 */

class File extends Model
{
    protected $insert = [ 'create_time'=>NOW_TIME, ];

    /**
     * 文件上传
     * @param  array  $files   要上传的文件列表（通常是$_FILES数组）
     * @param  array  $setting 文件上传配置
     * @param  string $driver  上传驱动名称
     * @param  array  $config  上传驱动配置
     * @return array           文件上传成功后的信息
     */
    public function upload($files, $setting, $driver = 'Local', $config = null){
        /* 上传文件 */
        $setting['callback'] = array($this, 'isFile');
		$setting['removeTrash'] = array($this, 'removeTrash');
        $Upload = new Upload($setting, $driver, $config);
        $info   = $Upload->upload($files);

        if($info){ //文件上传成功，记录文件信息
            foreach ($info as $key => &$value) {
                /* 已经存在文件记录 */
                if(isset($value['id']) && is_numeric($value['id'])){
                    continue;
                }
                /* 记录文件信息 */
                $data = array(
                    'name' => $value['name'],
                    'savename' => $value['savename'],
                    'savepath' => $value['savepath'],
                    'ext' => $value['ext'],
                    'type' => $value['type'],
                    'size' => $value['size'],
                    'md5' => $value['md5'],
                    'sha1' => $value['sha1'],
                    'location' => strtolower($driver) ? 1 : 0,
                );

                $id = $this->create($data);
                if($id){
                    $value['id'] = $id;
                } else {
                    //TODO: 文件上传成功，但是记录文件信息失败，需记录日志
                    unset($info[$key]);
                }
            }
            return $info; //文件上传成功
        } else {
            $this->error = $Upload->getError();
            return false;
        }
    }

    /**
     * 下载指定文件
     * @param  number  $root 文件存储根目录
     * @param  integer $id   文件ID
     * @param  string  $args 回调函数参数
     * @return boolean       false-下载失败，否则输出下载文件
     */
    public function download($root, $id, $callback = null, $args = null){
        /* 获取下载文件信息 */
        $file = $this->get($id);
        if(!$file){
            return $this->error = '不存在该文件！';
        }

        /* 下载文件 */
        switch ($file['location']) {
            case 0: //下载本地文件
                $file['rootpath'] = $root;
                return $this->downLocalFile($file, $callback, $args);
			case 1: //下载FTP文件
				$file['rootpath'] = $root;
				return $this->downFtpFile($file, $callback, $args);
                break;
            default:
                return $this->error = '不支持的文件存储类型！';
        }
    }

    /**
     * 检测当前上传的文件是否已经存在
     * @param  array   $file 文件上传数组
     * @return boolean       文件信息， false - 不存在该文件
     * @throws \Exception
     */
    public function isFile($file){
        if(empty($file['md5'])){
            throw new \Exception('缺少参数:md5');
        }
        /* 查找文件 */
        $map = array('md5' => $file['md5'],'sha1'=>$file['sha1']);
        return $this::get($map);
    }

    /**
     * 下载本地文件
     * @param  array    $file     文件信息数组
     * @param  callable $callback 下载回调函数，一般用于增加下载次数
     * @param  string   $args     回调函数参数
     * @return boolean            下载失败返回false
     */
    private function downLocalFile($file, $callback = null, $args = null){
        if(is_file($file['rootpath'].$file['savepath'].$file['savename'])){
            /* 调用回调函数新增下载数 */
            is_callable($callback) && call_user_func($callback, $args);

            /* 执行下载 */ //TODO: 大文件断点续传
            header("Content-Description: File Transfer");
            header('Content-type: ' . $file['type']);
            header('Content-Length:' . $file['size']);
            if (preg_match('/MSIE/', $_SERVER['HTTP_USER_AGENT'])) { //for IE
                header('Content-Disposition: attachment; filename="' . rawurlencode($file['name']) . '"');
            } else {
                header('Content-Disposition: attachment; filename="' . $file['name'] . '"');
            }
            readfile($file['rootpath'].$file['savepath'].$file['savename']);
            exit;
        } else {
            return $this->error = '文件已被删除！';
        }
    }

	/**
	 * 下载ftp文件
	 * @param  array    $file     文件信息数组
	 * @param  callable $callback 下载回调函数，一般用于增加下载次数
	 * @param  string   $args     回调函数参数
	 * @return boolean            下载失败返回false
	 */
	private function downFtpFile($file, $callback = null, $args = null){
		/* 调用回调函数新增下载数 */
		is_callable($callback) && call_user_func($callback, $args);

		$host = C('DOWNLOAD_HOST.host');
		$root = explode('/', $file['rootpath']);
		$file['savepath'] = $root[3].'/'.$file['savepath'];

		$data = array($file['savepath'], $file['savename'], $file['name'], $file['mime']);
		$data = json_encode($data);
		$key = think_encrypt($data, C('DATA_AUTH_KEY'), 600);

		header("Location:http://{$host}/onethink.php?key={$key}");
	}

	/**
	 * 清除数据库存在但本地不存在的数据
	 * @param $data
	 */
	public function removeTrash($data){
		$this->delete($data['id']);
	}

}
