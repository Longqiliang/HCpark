<?php
/**
 * Created by PhpStorm.
 * User: Scy
 * QQ: 329880211@qq.com
 * Date: 2016/12/9
 * Time: 10:38
 */

namespace app\card\model;

use think\Model;
use think\Log;

/**
 * Class Picture
 * @package app\card\model
 */
class Picture extends Model
{
    /**
     * @param $id
     * @return bool|mixed
     */
    public function getPathById($id)
    {
        $data = $this->where('id', $id)->field(['path'])->find();
        if (empty($data)) {
            Log::error('无法获取图片路径 pid:'.$id);
            return false;
        } else {
            return $data['path'];
        }
    }

    /**
     * @param $path
     * @return bool|mixed
     */
    public function savePath($path)
    {
        $data['path'] = $path;
        $res = $this->isUpdate(false)->create($data);
        if ($res) {
            return $res['id'];
        } else {
            Log::error(" error_msg:".$this->getError());
            return false;
        }
    }
    /**
     * 获取帖子图片路径
     *
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getCardPath()
    {
        $map['path'] = array('like', '%card%');
        $data = $this->where($map)->select();
        return $data;
    }
}