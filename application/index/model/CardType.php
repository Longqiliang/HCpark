<?php
/**
 * Created by PhpStorm.
 * User: Scy
 * QQ: 329880211@qq.com
 * Date: 2016/12/14
 * Time: 1:53
 */

namespace app\index\model;

use think\Db;
use think\Model;

/**
 * 获取分类
 *
 * Class CardType
 * @package app\card\model
 */
class CardType extends Model
{
    /**
     * 获取分类列表
     *
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getTypeList()
    {
        $park_id = session("park_id");
        $map = ['park_id' => $park_id ,'status' => 1];
        $data = Db::name('card_type')->where($map)->field('id,interest')->select();

        return $data;
    }
    /**
     * 获取type
     *
     * @param $id
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getTypeById($id)
    {
        $map['id'] = $id;
        $map['status'] = 1;
        return $this->where($map)->select();
    }
}