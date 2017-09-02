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
        $userinfo = $user->where('userid', $user_id)->find();
        if (empty($userinfo['header'])) {
            $header = $userinfo['avatar'];
        } else {
            $header = $userinfo['header'];

        }

        $this->assign('header', $header);
        $this->assign('list', $groupList);

        return $this->fetch();
    }

    /*个人*/
    public function personal()
    {
        $userid = session('userId');
        $cuser = new CommunicateUser();
        $map = [
            'user_id' => $userid,
            'status' => 3
        ];

        $count = $cuser->where($map)->count();
        //0 不是管理员 1 是管理员
        $is_manageer = $count > 0 ? 1 : 0;

        $this->assign('is_manage', $is_manageer);
        return $this->fetch();
    }

    /*加入*/
    public function join()
    {
        $cgroup = new CommunicateGroup();
        $wechat = new WechatUser();
        $cuser = new CommunicateUser();
        if (IS_POST) {
            $user_id = session('userId');
            $remark = input('remark');
            $map = [
                'group_id' => input('group_id'),
                'user_id' => $user_id,
                'status' => array('gt', 0)
            ];
            $is_join = $cuser->where($map)->find();
            if ($is_join) {
                return $this->error("已经加入或者正在审核中");
            } else {
                $map['status'] = 1;
                $map['remark'] = $remark;
                $reult = $cuser->save($map);
                if ($reult) {
                    return $this->success("成功");
                } else {
                    return $this->error("申请失败");
                }
            }
        } else {
            $group_id = input('group_id');
            $user_id = session('userId');
            $map = [
                'group_id' => $group_id,
                'user_id' => $user_id,
                'status' => array('gt', 0)
            ];
            $groupinfo = $cgroup->where('id', $group_id)->find();
            $status = $cuser->where($map)->field('status')->find();
            $groupinfo['is_join'] = $status['status'] ? $status['status'] : 0;
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
        $groupInfo = $cgroup->where('id', input('group_id'))->find();
        $postsList = array();
        foreach ($posts as $value) {
            $data = [
                'name' => isset($value->user->name) ? $value->user->name : "",
                'title' => $value['title'],
                'content' => $value['content'],
                'img' => !empty($value['img']) ? json_decode($value['img']) : "",
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

        echo json_encode($postsList);
        $this->assign('list', $postsList);
        $this->assign('group', $groupInfo);
        return $this->fetch();
    }

    /*帖子详情*/
    public function postDetails()
    {
        $weuser = new  WechatUser();
        $post = CommunicatePosts::get(input('id'));
        $post['img'] = !empty($value['img']) ? json_decode($value['img']) : "";
        $post['user_name'] = isset($post->user->name) ? $post->user->name : "";
        $header = isset($post->user->header) ? $post->user->header : "";
        $avatar = isset($post->user->avatar) ? $post->user->avatar : "";
        $post['header'] = empty($header) ? $avatar : $header;
        unset($post['user']);
        $this->assign('post', $post);
        // 评论列表
        $map = [
            'target_id' => input('id')
        ];
        $comments = CommunicateComment::where($map)->order('id desc')->limit(6)->select();
        //$count = CommunicateComment::where($map)->count();

        echo json_encode($post);
        echo json_encode($comments);
        $this->assign('comments', $comments);
        return $this->fetch();
    }

    /*写帖子页面*/
    public function writePost()
    {
        $post = new CommunicatePosts();
        if (IS_POST) {
            $data = input('');
            $map = [
                'title' => $data['title'],
                'content' => $data['content'],
                'img' => json_encode($data['img']),
                'user_id' => session('userId'),
                'group_id' => $data['group_id']
            ];
            $result = $post->save($map);
            if ($result) {
                return $this->success("成功");
            } else {

                return $this->error("失败");
            }

        } else {
            $group_id = input('group_id');
            $this->assign('group_id', $group_id);
            return $this->fetch();
        }


    }


    /*评论*/
    public function comment()
    {
        $userinfo = WechatUser::where('userid', session('userId'))->field('name,header,avatar')->find();
        $data = [
            'target_id' => input('id'),
            'user_id' => session('userId'),
            'content' => input('content'),
        ];
        if (!empty($userinfo['header'])) {
            $data['header'] = $userinfo['header'];
        } else {
            $data['header'] = !empty($userinfo['avatar']) ? $userinfo['avatar'] : "";
        }
        $post = CommunicatePosts::get(input('id'));
        $data['user_name'] = $userinfo['name'];
        $result = CommunicateComment::create($data);
        if ($result) {
            $post['comments']+=1;
            $post->save();
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

        return json(['total' => count($comments), 'comments' => $comments]);
    }


    /*我的申请*/
    public function myApplication()
    {
        $userid = session('userId');
        $cuser = new CommunicateUser();
        $cgroup = new CommunicateGroup();
        $map = [
            'user_id' => $userid,
            'status' => array('lt', 3)
        ];
        $list = $cuser->where($map)->select();
        foreach ($list as $value) {
           $data =$data = $cgroup->get($value['group_id']);
            $value['group_name'] = isset($data['group_name']) ? $data['group_name']: "";
        }
        $this->assign('list', $list);

        return $this->fetch();
    }

    /*我的审核*/
    public function myCheck()
    {
        $userid = session('userId');
        $cuser = new CommunicateUser();
        //TODO  找他所有管理群的ID，再通过这个ID数组去把所有所属组的申请信息合在一起传出去



        $map = [
            'user_id' => $userid,
            'status' => array('lt', 3)
        ];
        $list = $cuser->where($map)->select();
        foreach ($list as $value) {
            $value['group_name'] = isset($value->group->group_name) ? $value->group->group_name : "";
        }


        return $this->fetch();
    }

    /*我的发布*/
    public function myRelease()
    {
        $userid = session('userId');
        $posts = new CommunicatePosts();
        $myPosts = $posts->where('user_id', $userid)->select();
        $postsList = array();
        foreach ($myPosts as $value) {
            $data = [
                'name' => isset($value->user->name) ? $value->user->name : "",
                'title' => $value['title'],
                'content' => $value['content'],
                'img' => !empty($value['img']) ? json_decode($value['img']) : "",
                'comments' => $value['comments'],
                'create_time' => $value['create_time']
            ];
            $avatar = isset($value->user->avatar) ? $value->user->avatar : "";
            $header = isset($value->user->header) ? $value->user->header : "";
            $data['header'] = empty($header) ? $avatar : $header;
            array_push($postsList, $data);
        }
        echo  json_encode($postsList);
        $this->assign('list', $postsList);
        return $this->fetch();
    }

    /*我的评论*/
    public function myComment()
    {
        $ccomment =new CommunicateComment();
        $cpost = new  CommunicatePosts();
        $userid =session('userId');
        $commments =$ccomment->where('user_id',$userid)->select();
        $group_ids=array();
        foreach ($commments as $value){
         array_push($group_ids,$value['target_id']);
        }
        echo json_encode($group_ids);
        $ids = array_values(array_unique($group_ids));
        $data['']=
        $list =$cpost->where()->select();
        echo json_encode($list);


        return $this->fetch();
    }
}