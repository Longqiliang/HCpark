<?php
/**
 * Created by PhpStorm.
 * User: Scy
 * QQ: 329880211@qq.com
 * Date: 2016/12/9
 * Time: 10:38
 */

namespace app\index\model;

use think\Model;

/**
 * 论坛模块
 *
 * Class Card
 * @package app\card\model
 */
class Card extends Model
{
    protected $autoWriteTimestamp = 'int';
    /**
     * 获取帖子列表
     * @param $number
     * @param $update_time
     * @param $type
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getCardList($number, $update_time, $type)
    {
        $map = [];
        if (!empty($update_time)) {
            $map['cd.update_time'] = array('lt', $update_time);
        }
        if (!empty($type)) {
            $map['cd.type'] = $type;
        }
        $map['cd.status'] = 1;
        $order = array('update_time desc');
        $filed = array('ct.interest', 'cd.id', 'cd.title', 'cd.content', 'cd.list_img', 'up.nickname', 'up.headimgurl', 'up.sex', 'cd.update_time', 'cd.comments', 'cd.likes');
        $data = $this->alias('cd')
            ->join('user_profile up','up.uid = cd.uid')
            ->join('card_type ct', 'cd.type = ct.id and ct.status = 1')
            ->where($map)
            ->field($filed)
            ->order($order)
            ->limit(0,$number)
            ->select();
        return $data;
    }
    /**
     * 新增帖子
     *
     * @param $user_id
     * @param $type
     * @param $title
     * @param $content
     * @param $front_cover
     * @param $pic_data
     * @return $this
     */
    public function addNewCard($user_id, $type, $title, $content, $front_cover, $pic_data)
    {
        $data['uid']     = $user_id;
        $data['type']    = $type;
        $data['title']   = $title;
        $data['content'] = $content;
        $data['park_id'] = session("park_id");
        if (!empty($front_cover)) {
            $data['front_cover'] = $front_cover;
        }
        if (!empty($pic_data)) {
            $data['list_img'] = $pic_data;
        }
        return $this->isUpdate(false)->create($data);
    }
    /**
     * @param $user_id
     * @param $number
     * @param $update_time
     * @param $status
     * @param $extra
     * @return mixed
     */
    public function getUserCardList($user_id, $number, $update_time, $status = 0, $extra = "")
    {
        $map = [];
        if (!empty($update_time)) {
            $map['cd.update_time'] = array('lt', $update_time);
        }
        $map['cd.uid'] = $user_id;
        if (empty($status)) {
            $map['cd.status'] = array('in', array(1,2));
        } else {
            $map['cd.status'] = 0;
        }
        if (!empty($extra)) {
            $map['cd.status'] = $extra;
        }
        $filed = array('cd.status', 'p.path', 'cd.id', 'cd.title', 'cd.update_time');
        $order = array('update_time desc');
        $data = $this->alias('cd')
            ->join('picture p','p.id = cd.front_cover', 'left')
            ->where($map)
            ->field($filed)
            ->limit(0,$number)
            ->order($order)
            ->select();
        return $data;
    }
    /**
     * @param $article_id
     * @return mixed
     */
    public function getDetailById($article_id)
    {
        $field = array('up.sex', 'up.headimgurl', 'up.nickname', 'c.title', 'c.list_img', 'c.content', 'ct.interest', 'c.update_time', 'c.likes', 'c.comments');
        $data = $this->alias('c')
            ->join('user u', 'u.id = c.uid and u.status = 1')
            ->join('user_profile up','up.uid = c.uid')
            ->join('card_type ct','ct.id = c.type and ct.status = 1')
            ->where('c.id',$article_id)
            ->field($field)
            ->find();
        return $data;
    }
    /**
     * 删除帖子
     *
     * @param $id
     * @return mixed
     */
    public function deleteCard($id)
    {
        $data['status'] = -1;
        return $this->isUpdate(true)->where('id',$id)->update($data);
    }

    public function getUserHeader(){

        return $this->hasOne("WechatUser",'userid','uid')->field('id,name,avatar,header');
    }

}