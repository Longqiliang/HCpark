<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/8/31
 * Time: 上午9:22
 */

namespace app\index\controller;

use app\index\model\CommunicateGroup;
use app\index\model\CommunicateUser;
use app\index\model\WechatUser;
use app\index\model\CommunicatePosts;
use app\index\model\CommunicateComment;

class Communication extends Base
{
    /*首页*/
    public function index()
    {
        $user_id = session('userId');
        $cgroup = new CommunicateGroup();
        $cuser = new CommunicateUser();
        $user = new WechatUser();
        $map2 = [
            'status' => 1,
            'park_id' => session('park_id')
        ];
        $groupList = $cgroup->where($map2)->select();
        $map = [
            'user_id' => $user_id,
            'status' => array('gt', 1),

        ];
        foreach ($groupList as $value) {
            $map['group_id'] = $value['id'];
            $is_join = $cuser->where($map)->find();
            //0 未加入  1 已经加入
            $value['is_join'] = $is_join ? 1 : 0;

        }
        $userinfo =$user->where('userid',$user_id)->find();
        if(empty($userinfo['header'])){
            $header=$userinfo['avatar'];
        }else{
            $header=$userinfo['header'];

        }

        $this->assign('header',$header);
        $this->assign('list', $groupList);

        return $this->fetch();
    }

    /*个人*/
    public function personal()
    {


        return $this->fetch();
    }

    /*申请加入*/
    public function application()
    {
        $group_id = input('group_id');
        $user_id = session('userId');
        $cgroup = new CommunicateGroup();
        $wechat = new WechatUser();
        $groupinfo = $cgroup->where('id', $group_id)->find();
        $user = $wechat->where('userid', $user_id)->find();
        $userinfo = [
            'name' => $user['name'],
            'mobile' => $user['mobile'],
            'department' => isset($user->departmentName->name) ? $user->departmentName->name : ""
        ];
        $this->assign('group', $groupinfo);
        $this->assign('user', $userinfo);
        return $this->fetch();
    }

    /*加入*/
    public function join()
    {
        $user_id = session('userId');
        $remark = input('remark');
        $cuser = new CommunicateUser();
        $map = [
            'group_id' => input('group_id'),
            'user_id' => $user_id,
            'status' => array('gt', 0)
        ];
        $is_join = $cuser->where($map)->find();
        if ($is_join) {
            return $this->error("已经加入或者正在审核中");
        } else {
            $map['status'] = 0;
            $map['remark'] = $remark;
            $reult = $cuser->save($map);
            if ($reult) {
                return $this->success("成功");
            } else {
                return $this->error("申请失败");
            }
        }
    }
    /*帖子列表*/
    public function postsList()
    {
        $cp = new CommunicatePosts();
        $cgroup = new CommunicateGroup();
        $map = [
            'group_id' => input('group_id'),
            'status' => 1
        ];
        $posts = $cp->where($map)->select();
        $groupInfo = $cgroup->where('group_id', input('group_id'))->find();
        $postsList = array();
        foreach ($posts as $value) {
            $data = [
                'name' => isset($value->user->name) ? $value->user->name : "",
                'title' => $value['title'],
                'content' => $value['content'],
                'img' => $value['img'],
                'comments' => $value['comments'],
                'create_time' => $value['create_time']
            ];
            $avatar = isset($value->user->avatar) ? $value->user->avatar : "";
            $header = isset($value->user->header) ? $value->user->header : "";
            $data['header'] = empty($header) ? $avatar : $header;
            array_push($postsList, $data);
        }
        unset($groupInfo['status']);
        unset($groupInfo['content']);
        $this->assign('list', $postsList);
        $this->assign('group', $groupInfo);
        return $this->fetch();
    }

    /*帖子详情*/
    public function postDetails()
    {

        return $this->fetch();
    }

    /*写帖子页面*/
    public function writePost()
    {
        $group_id = input('group_id');
        $this->assign('group_id', $group_id);
        return $this->fetch();

    }

    /*写*/
    public function write()
    {
        $post = new CommunicatePosts();
        $data = input('');
        $map = [
            'title' => $data['title'],
            'content' => $data['content'],
            'img' => $data['img'],
            'user_id' => session('userId'),
            'group_id' => $data['group_id']
        ];
        $result = $post->save($map);
        if ($result) {
            return $this->success("成功");
        } else {

            return $this->error("失败");
        }

    }

    /*评论*/
    public function comment()
    {
        $data = [
            'target_id' => input('group_id'),
            'user_id' => session('userId'),
            'content' => input('content'),
        ];
        $userinfo = WechatUser::where('userid', session('userId'))->field('name,header,avatar')->find();
        $data['user_name'] = $userinfo['name'];
        $result = CommunicateComment::create($data);
        if (!empty($userinfo['header'])) {

            $result['header'] = $userinfo['header'];

        } else {
            $result['header'] = !empty($userinfo['avatar']) ? $userinfo['avatar'] : "";

        }
        if ($result) {

            return $this->success('评论成功', '', $result);
        } else {

            return $this->error('评论失败');
        }

    }


    //评论分页
    public function moreComment()
    {
        $lastId = input('lastId', 0);
        $map = [
            'target_id' => input('group_id')
        ];
        if ($lastId != 0) {
            $map['id'] = ['<', $lastId];
        }
        $comments = CommunicateComment::where($map)->order('id desc')->limit(6)->select();
        foreach ($comments as $value) {
            $userinfo['header'] = isset($value->wechatuser->header) ? $value->wechatuser->header : "";
            $userinfo['avatar'] = isset($value->wechatuser->avatar) ? $value->wechatuser->avatar : "";
            if (!empty($userinfo['header'])) {

                $value['header'] = $userinfo['header'];

            } else {
                $value['header'] = $userinfo['avatar'];

            }
        }
        return json(['total' => count($comments), 'comments' => $comments]);
    }


    /*我的申请*/
    public function myApplication()
    {

        return $this->fetch();
    }

    /*我的审核*/
    public function myCheck()
    {

        return $this->fetch();
    }

    /*我的发布*/
    public function myRelease()
    {

        return $this->fetch();
    }

    /*我的评论*/
    public function myComment()
    {

        return $this->fetch();
    }
}