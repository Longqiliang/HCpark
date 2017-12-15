<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/8/31
 * Time: 下午2:22
 */

namespace app\admin\controller;

use app\common\model\CommunicateGroup;
use app\common\model\CommunicateUser;
use app\common\model\CommunicatePosts;
use app\common\model\CommunicateComment;
use app\common\model\WechatUser;
use think\Db;
use wechat\TPWechat;
use think\Loader;

//合作交流
class Communication extends Admin
{   //主页
    public function index()
    {
        $paek_id = session('user_auth')['park_id'];
        $CommunicateGroup = new CommunicateGroup();
        $map = [
            'park_id' => $paek_id,
            'status' => array('neq',-1)
        ];
        $serch = input('search');
        if (!empty($serch)) {
            $map['group_name'] = array('like', '%' . $serch . "%");
        }
        $list = $CommunicateGroup->where($map)->paginate();
        int_to_string($list, $map = array('status' => array(0 => '禁用', 1 => '启用'), 'park_id' => array(3 => '希垦园区')));
        $this->assign('search', $serch);
        $this->assign('park_id',$paek_id);
        $this->assign('list', $list);
        return $this->fetch();
    }

    public function edit()
    {

        $cgroup = new CommunicateGroup();
        $data = input('');
        $id = isset($data['id'])?$data['id']:"";
        if($id) {
            $reult = $cgroup->save($data, ['id' => $id]);
        }else{
            unset($data['id']);

            $reult = $cgroup->validate(true)->save($data);
        }
        if ($reult) {
            return $this->success("成功");

        } else {

            return $this->error($cgroup->getError());
        }
    }
    //查看成员
    public function showUsers()
    {
        $cuser = new CommunicateUser();
        $user = new  WechatUser();
        if (IS_POST) {
            $id = input('id');
            $status=input('status');
            if($status==-1){
              $msg="未通过";

            }else{
                $msg="通过";
            }
            $remark=input('remark');
            $result = $cuser->where('id', $id)->update(['status' => $status,'remark'=>$remark]);
            if ($result) {
               $user=$cuser->where('id',$id)->find();
                Loader::import('wechat\TPWechat', EXTEND_PATH);
                $weObj = new TPWechat(config('Communication'));
                $groupname=isset($user->group->group_name)?$user->group->group_name:"";
                $data = [
                    "touser" => $user['user_id'],
                    'safe' => 0,
                    'msgtype' => 'text',
                    'agentid' => 1000013,
                    'text' => [
                        'title' => "加群申请",
                        'content' => '您申请加入“'.$groupname.'”群，审核结果：'.$msg.'',

                    ]
                ];
                $result1 = $weObj->sendMessage($data);
                //var_dump($result1);
                //var_dump($weObj->errCode.'|'.$weObj->errMsg);
                if ($result1['errcode'] == 0) {
                    return $this->success('提交成功');
                } else {
                    return $this->error('推送失败');
                }



                return $this->success("成功");


            } else {
                return $this->error("失败");
            }
        } else {

            $id = input('id');

            $serch = input('search');
            if (!empty($serch)) {
                $map['name'] = array('like', '%' . $serch . "%");
                $userlist = $user->where($map)->order('create_time desc')->select();
                $ids = array();
                foreach ($userlist as $value) {
                    array_push($ids, $value['userid']);
                }
                if (count($ids) > 0) {
                    $data['user_id'] = array('in', $ids);
                }
            }

            $data['group_id'] = $id;
            $data['status'] = ['>',-1];
            $list = $cuser->where($data)->paginate();
            int_to_string($list, $map = array('status' => array(-1 => '申请失败', 1 => '申请中', 2 => '普通成员', 3 => '管理员')));
            foreach ($list as $value) {
                $value['user_name'] = isset($value->user->name) ? $value->user->name : "";
                $value['company'] = isset($value->user->departmentName->name) ? $value->user->departmentName->name : "";
                $value['mobile'] = isset($value->user->mobile) ? $value->user->mobile : "";
            }
            $this->assign('group_id',$id );
            $this->assign('search', $serch);
            $this->assign('list', $list);
            return $this->fetch();
        }
    }
    //查看帖子
    public function showPosts(){
        $posts = new CommunicatePosts();
        $user = new  WechatUser();


            $id = input('id');
            $serch = input('search');
            if (!empty($serch)) {
                $data['title'] = array('like', '%' . $serch . "%");

            }
            $data['group_id'] = $id;
            $list = $posts->where($data)->where('status','neq',-1)->paginate();
            foreach ($list as $value){
                $value['username']=isset($value->user->name)?$value->user->name:"";


            }
            int_to_string($list, $map = array('status' => array(-1 => '删除', 0=> '审核中', 1 => '审核成功', 2 => '审核失败')));
        $this->assign('group_id',$id );
            $this->assign('search', $serch);
            $this->assign('list', $list);
            return $this->fetch();
    }

    //逻辑删除
    public function deletePost() {
        $ids = input('ids/a');

        $result = CommunicatePosts::where('id', 'in', $ids)->update(['status' => -1]);
        if($result) {
            return $this->success('删除成功');
        } elseif(!$result) {
            return $this->error('删除失败');
        }
    }
    //逻辑删除帖子
    public function deleteGroup() {
        $ids = input('ids/a');

        $result = CommunicateGroup::where('id', 'in', $ids)->update(['status' => -1]);
        if($result) {
            return $this->success('删除成功');
        } elseif(!$result) {
            return $this->error('删除失败');
        }
    }
    //逻辑删除评论
    public function deleteComment() {
        $ids = input('ids/a');
        $result = CommunicateComment::where('id', 'in', $ids)->update(['status' => -1]);
        $list =CommunicateComment::where('id', 'in', $ids)->find();
        foreach ($list as $value){
            CommunicatePosts::Where('id',$value['target_id'])->setDec('comment',1);
        }
        if($result) {
            return $this->success('删除成功');
        } elseif(!$result) {
            return $this->error('删除失败');
        }
    }

    //帖子详情
    public function detail()
    {
        $id=input('id');
        $result=Db::table('tb_communicate_posts')
            ->alias('p')
            ->join('__COMMUNICATE_GROUP__ g', 'p.group_id=g.id')
            ->join('__WECHAT_USER__ u', 'p.user_id=u.userid')
            ->field('g.group_name,u.name')
            ->where('p.id','eq',$id)
            ->find();
        $res= CommunicatePosts::get($id);
        $res['img']=json_decode($res['img'],true);
        $res['group_name']=$result['group_name'];
        $res['name']=$result['name'];
        $this->assign('res',$res);

        return $this->fetch();
    }
    //查看帖子评论
    public function showComment(){
        $posts = new CommunicateComment();
        // 评论列表
        $map = [
            'target_id' => input('id'),
            'status'=>array('neq',-1)
        ];
        $comments = CommunicateComment::where($map)->order('id desc')->paginate();
        foreach ($comments as $value) {
            $userinfo = WechatUser::where('userid', $value['user_id'])->field('header,avatar')->find();
            $head = isset($userinfo['header']) ? $userinfo['header'] : "";
            $ava = isset($userinfo['avatar']) ? $userinfo['avatar'] : "";
            $value['header'] = empty($head) ? $ava : $head;
            $value['create_time'] = date("Y-m-d H:m", $value['create_time']);
        }
        //$count = CommunicateComment::where($map)->count();
        //echo json_encode($post);
        //echo json_encode($comments);
        $this->assign('list', $comments);

        return $this->fetch();
    }


    /*修改状态值*/
    public function changeStatus(){
        $id = input('id');
        $uid = input('uid');
        $res = CommunicatePosts::where('id',$id)->update(['status'=>$uid]);
        if ($res){

            $this->success("审核成功");
        }else{

            $this->error("审核失败");
        }

    }

    //查看成员逻辑删除
    public function moveToTrash() {
        $ids = input('ids/a');
        $result = CommunicateUser::where('id', 'in', $ids)->update(['status' => -1]);
        if($result) {
            return $this->success('删除成功');
        } elseif(!$result) {
            return $this->error('删除失败');
        }
    }





}