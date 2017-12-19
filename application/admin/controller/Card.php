<?php
/**
 * Created by PhpStorm.
 * User:贡7
 */

namespace app\admin\controller;

use app\admin\model\Card as CardModel;
use app\index\model\CardType;
use app\common\model\WechatUser;
use app\admin\model\Comments;
use think\Db;
use think\Exception;
use think\Log;
use app\common\behavior\Service as ServiceModel;

/**
 * Class Card
 * @package app\admin\controller
 * 帖子管理
 */
class Card extends Admin
{
    /**
     * 帖子列表
     */
    public function cardIndex()
    {
        $cardModel = new CardModel();
        $typeModel = new CardType();
        $userModel = new WechatUser();
        $park_id = session("user_auth")['park_id'];
        $map = array(
            'status' => array('egt', 0),
            'park_id' => $park_id,
        );
        $list = $cardModel->where($map)->order('id desc')->paginate(12);
        int_to_string($list, array(
            'status' => array(0 => "待审核", 1 => "审核通过", 2 => "审核不通过"),
        ));
        foreach ($list as $value) {
            $type = json_decode($value['type']);
            $value['type_name'] = implode("#",$typeModel->getCardTypeById($type));
            $user = $userModel->where(['userid' => $value['uid']])->find();
            $value['username'] = $user['name'];
        }
        $this->assign('list', $list);

        return $this->fetch();
    }

    /**
     * 帖子预览
     */
    public function preview()
    {
        $cardModel = new CardModel();
        $typeModel = new CardType();
        $userModel = new WechatUser();
        $id = input('id');
        $msg = $cardModel->get($id);
        $msg['list_img'] = json_decode($msg['list_img']);
        $type = json_decode($msg['type']);
        $msg['type_name'] = implode("#",$typeModel->getCardTypeById($type));

        $user = $userModel->where(['userid' => $msg['uid']])->find();
        $msg['username'] = $user['name'];

        $this->assign('msg', $msg);

        return $this->fetch();
    }

    /**
     * 帖子审核
     */
    public function review()
    {
        $cardModel = new CardModel();
        $status = input('status');
        $id = input('id');
        $map['status'] = $status;
        $map['update_time'] = time();
        $map['remark'] = input('remark');
        $info = $cardModel->where('id', $id)->update($map);
        $cardInfo = $cardModel->where(['id' => $id])->find();
        $userId = $cardInfo['uid'];
        if ($info) {
            if ($status == 2) {
                //推送
                $message = [
                    "title" => "论坛帖子提示",
                    "description" => date('m月d日', time()) . "\n帖子审核失败\n备注:" . $cardInfo['remark'],
                    "url" => 'http://' . $_SERVER['HTTP_HOST'] . '/index/CardDetail/getDetail/id/' . $id,
                ];
                ServiceModel::sendPersonalMessage($message, $userId);
            }

            return $this->success("审核成功", Url('Card/cardindex'));
        } else {

            return $this->error("审核失败");
        }
    }

    /**
     * 删除帖子接口
     *
     * @return mixed
     */
    public function delete()
    {
        $id = input('id');
        if (empty($id)) {
            return $this->error('删除失败', 'cardindex');
        }
        $cardModel = new CardModel();
        Db::startTrans();
        try {
            $save_data['status'] = -1;
            $res = $cardModel->where('id', $id)->update($save_data);
            if (!$res) {
                throw new Exception($cardModel->getError());
            }
            $commentModel = new Comments();
            $map1['aid'] = $id;
            $map1['status'] = 1;
            $res = $commentModel->where($map1)->find();
            if ($res) {
                $map['aid'] = $id;
                $map['table'] = 'card';
                $result = $commentModel->where($map)->update($save_data);
                if (!$result) {
                    throw new Exception($commentModel->getError());
                }
            }
            Db::commit();
        } catch (Exception $ex) {
            Db::rollback();
            Log::error("[帖子删除失败] error_message:" . $ex->getMessage());

            return $this->error('删除失败', Url('Card/cardindex'));
        }

        return $this->success('删除成功', Url('Card/cardindex'));
    }

    /**
     * 标签类别列表
     */
    public function typeIndex()
    {
        $typeModel = new CardType();
        $park_id = session("user_auth")['park_id'];
        $map = array(
            'status' => array('eq', 1),
            'park_id' => $park_id,
        );
        $list = $typeModel->where($map)->order('sort asc')->order('id desc')->paginate(12);
        int_to_string($list, array(
            'status' => array(1 => "已发布"),
        ));
        $this->assign('list', $list);

        return $this->fetch();
    }

    /**
     * 标签新增
     */
    public function typeAdd()
    {
        $typeModel = new CardType();
        if (IS_POST) {
            $data = input('post.');
            $data['park_id'] = session("user_auth")['park_id'];
            $id = $typeModel->validate('CardType')->save($data);
            if ($id) {

                return $this->success("新增成功");
            } else {

                return $this->error($typeModel->getError());
            }
        } else {
            $this->assign('msg', '');

            return $this->fetch('typeedit');
        }
    }

    /**
     * 标签修改
     */
    public function typeEdit()
    {
        $typeModel = new CardType();
        if (IS_POST) {
            $data = input('post.');
            $id = $typeModel->validate('CardType')->save($data, ['id' => $data['id']]);
            if ($id) {

                return $this->success("更改成功");
            } else {

                return $this->error($typeModel->getError());
            }
        } else {
            $id = input('id');
            $msg = $typeModel->get($id);
            $this->assign('msg', $msg);

            return $this->fetch();
        }
    }

    /**
     * 标签删除
     */
    public function typeDel()
    {
        $typeModel = new CardType();
        $id = input('id');
        $map['status'] = -1;
        $info = $typeModel->where('id', $id)->update($map);
        if ($info) {

            return $this->success("删除成功");
        } else {

            return $this->error("删除失败");
        }

    }

    /*
     * 帖子评论列表
     * */
    public function comments()
    {
        $map = [
            'aid' => input('id'),
            'status' => array('neq', -1),
        ];
        $commentModel = new Comments();
        $list = $commentModel->where($map)->order('status desc')->order('create_time desc')->paginate();

        $this->assign('list', $list);

        return $this->fetch();
    }

    /*
     * 删除帖子的评论
     * */
    public function commentsDel()
    {
        $map = [
            'status' => -1,
        ];
        $commentModel = new Comments();
        $info = $commentModel->where('id', input('id'))->update($map);
        if ($info) {

            return $this->success("删除成功");
        } else {

            return $this->error("删除失败");
        }
    }

    /*
     * 评论详情
     * */
    public function getInfo()
    {
        $commentModel = new Comments();
        $menu = $commentModel->where(['id'=>input('id')])->field('uid,status,content,create_time')->find();
        $menu['commentuser'] = isset($menu->getUserHeader->name)?$menu->getUserHeader->name:"";

        return json_encode($menu);
    }

    /*
     * 取消举报
     * */
    public function cancel()
    {
        $map = [
            'status' => 1,
        ];
        $commentModel = new Comments();
        $info = $commentModel->where('id', input('id'))->update($map);

        if ($info) {
            return $this->success("取消成功");
        } else {
            return $this->error("取消失败");
        }
    }
    /**
     *置顶
     */
    public function setTop(){
        $id = input("id");
        $uid = input("uid");
        $data = ['is_top' => $uid , "top_time" => time()];
        $card = new CardModel();
        $res = $card->where(['id' => $id])->update($data);
        if ($res){

            return $this->success("设置成功");
        }else{

            return $this->error("设置失败");
        }

    }

}
