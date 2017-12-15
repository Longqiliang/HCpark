<?php
/**
 * Created by PhpStorm.
 * User: Scy
 * QQ: 329880211@qq.com
 * Date: 2016/12/9
 * Time: 10:38
 */

namespace app\index\controller;


use app\index\model\Card as CardModel;
use app\index\model\CardType;
use app\index\model\Picture as PictureModel;
use app\admin\model\Comments;
use think\Db;


/**
 * 帖子详情
 *
 * Class CardDetail
 * @package app\card\controller
 */
class CardDetail extends Base
{
    /**
     * @return \think\response\Json
     */
    public function getDetail()
    {

        $id = input("id");
        $uid = session("userId");
        $card_model = new CardModel();
        $card_type = new CardType();
        $card_model->where(['id' => $id])->setInc('view', 1);
        $result = $card_model->where(['id' => $id])->find();
        $result['type'] = json_decode($result['type']);

        $result['type'] = $card_type->getCardTypeById($result['type']);
        if (empty($result)) {

            return sendErrorMessage("无法获取帖子内容", "70010");
        }
        $result['list_img'] = json_decode($result['list_img']);
        $result['name'] = isset($result->getUserHeader->name) ? $result->getUserHeader->name : "";
        $result['header'] = isset($result->getUserHeader->avatar) ? $result->getUserHeader->avatar : '';

        $collect = Db::name('collects')->where(['uid' => $uid, 'aid' => $id, 'status' => 1])->find();
        if ($collect) {
            $result['collect_status'] = 1;
        } else {
            $result['collect_status'] = 0;
        }
        $like = Db::name('like')->where(['uid' => $uid, 'aid' => $id, 'status' => 1])->find();
        if ($like) {
            $result['like_status'] = 1;
        } else {
            $result['like_status'] = 0;
        }
        unset($result['getUserHeader']);
        $result['comment_list'] = json_encode($this->commentList());

        $this->assign('list',$result);
        //return json_encode($result);

        return $this->fetch();
    }

    /**
     * 获取评论列表
     */
    public function commentList()
    {
        $len = input('len', 0);
        $aid = input('id');
        $map = ["aid" => $aid, 'status' => 1];
        $list = Comments::where($map)->order("id desc")->limit($len, 6)->select();
        foreach ($list as $k => $v) {
            $list[$k]['name'] = isset($v->getUserHeader->name)?$v->getUserHeader->name:"";
            $list[$k]['header'] = isset($v->getUserHeader->avatar) ? $v->getUserHeader->avatar : '';
            //$list[$k]['create_time'] = date('Y-m-d',$v['create_time']);
            unset($list[$k]['getUserHeader']);
        }
//
        return $list;
    }
    /**
     * 点赞模块
     */

    /**
     * 点赞
     *
     * @return \think\response\Json
     */
    public function setLike()
    {
        $card_model = new CardModel();
        $id = input("id");
        $uid = session("userId");
        $map = ['uid' => $uid, 'aid' => $id];
        $like = Db::name('like')->where($map)->find();
        if ($like) {
            $res = Db::name('like')->where(['id' => $like['id']])->update(['status' => 1, "create_time" => time()]);
        } else {
            $datas = ['uid' => $uid, 'aid' => $id, 'status' => 1, 'create_time' => time()];
            $res = Db::name('like')->insert($datas);
        }
        if ($res) {
            $card_model->where(['id' => $id])->setInc('likes',1);

            return $this->success("点赞成功");
        } else {

            return $this->error("点赞失败");
        }

    }

    /**
     * 取消点赞
     *
     * @return \think\response\Json
     */
    public function cancelLike()
    {
        $card_model = new CardModel();
        $id = input("id");
        $uid = session("userId");
        $map = ['uid' => $uid, 'aid' => $id, "status" => 1];
        $like = Db::name('like')->where($map)->find();
        if ($like) {
            $res = Db::name('like')->where(['id' => $like['id']])->update(['status' => 0, "create_time" => time()]);
            if ($res) {
                $card_model->where(['id' => $id])->setInc('likes',-1);

                return $this->success("取消点赞成功");
            } else {

                return $this->error("取消点赞失败");
            }
        } else {

            return $this->error("点赞帖子不存在");
        }

    }
    /**
     * 评论模块
     */

    /**
     * 评论
     *
     * @return \think\response\Json
     */
    public function setComment()
    {
        $card_model = new CardModel();
        $aid = input("id");
        $uid = session("userId");
        $content = input('content');
        $data = ['aid' => $aid, 'uid' => $uid, 'content' => $content, 'status' => 1, 'create_time' => time(), 'update_time' => time()];
        $res = Comments::create($data);
        if ($res) {
            $res['name'] = isset($res->getUserHeader->name) ? $res->getUserHeader->name : "";
            $res['header'] = isset($res->getUserHeader->avatar) ? $res->getUserHeader->avatar : "";
            $card_model->where(['id' => $aid])->setInc('comments',1);

            return $this->success("评论成功","",$res);
        } else {

            return $this->error("评论失败");
        }
    }








}
