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

//合作交流
class Communication extends Admin
{   //主页
    public function index()
    {
        $paek_id = session('user_auth')['park_id'];
        $CommunicateGroup = new CommunicateGroup();
        $map = [
            'park_id' => $paek_id
        ];
        $serch = input('search');
        if (!empty($serch)) {
            $map['group_name'] = array('like', '%' . $serch . "%");
        }
        $list = $CommunicateGroup->where($map)->paginate();
        int_to_string($list, $map = array('status' => array(0 => '禁用', 1 => '启用'), 'park_id' => array(3 => '希垦园区')));
        $this->assign('search', $serch);
        $this->assign('list', $list);
        return $this->fetch();
    }

    public function edit()
    {

        $cgroup = new CommunicateGroup();
        $data = input('');
        $id = $data['id'];
        if($id) {
            $reult = $cgroup->save($data, ['id' => $id]);
        }else{
            unset($data['id']);
            $reult = $cgroup->save($data);
        }
        if ($reult) {
            return $this->success("成功");

        } else {

            return $this->error("失败");
        }
    }

    public function showUsers()
    {

        $cuser = new CommunicateUser();
        $user = new  WechatUser();
        if (IS_POST) {
            $id = input('id');
            $result = $cuser->where('id', $id)->update(['status' => '2']);
            if ($result) {
                return $this->success("成功");
            } else {
                return $this->error("失败");
            }
        } else {

            $id = input('id');
            $serch = input('search');
            if (!empty($serch)) {

                $map['name'] = array('like', '%' . $serch . "%");
                $userlist = $user->where($map)->select();
                $ids = array();
                foreach ($userlist as $value) {
                    array_push($ids, $value['userid']);
                } 
                if (count($ids) > 0) {
                    $data['user_id'] = array('in', $ids);
                }
            }
            $data['group_id'] = $id;
            $list = $cuser->where($data)->paginate();
            echo json_encode($cuser->getLastSql());
            int_to_string($list, $map = array('status' => array(-1 => '申请失败', 1 => '申请中', 2 => '普通成员', 3 => '管理员')));
            foreach ($list as $value) {
                $value['user_name'] = isset($value->user->name) ? $value->user->name : "";
                $value['company'] = isset($value->user->departmentName->name) ? $value->user->departmentName->name : "";
                $value['mobile'] = isset($value->user->mobile) ? $value->user->mobile : "";
            }
            echo json_encode($id);
            $this->assign('group_id',$id );
            $this->assign('search', $serch);
            $this->assign('list', $list);
            return $this->fetch();
        }
    }
    public function showposts(){
        $posts = new CommunicatePosts();
        $user = new  WechatUser();


            $id = input('id');
            $serch = input('search');
            if (!empty($serch)) {
                $map['title'] = array('like', '%' . $serch . "%");
                $userlist = $user->where($map)->select();
                $ids = array();
                foreach ($userlist as $value) {
                    array_push($ids, $value['userid']);
                }
                if (count($ids) > 0) {
                    $data['user_id'] = array('in', $ids);
                }
            }
            $data['group_id'] = $id;
            $list = $posts->where($data)->where('status','neq',-1)->paginate();

            int_to_string($list, $map = array('status' => array(-1 => '删除', 1 => '审核中', 2 => '审核成功', 3 => '审核失败')));

            $this->assign('search', $serch);
            $this->assign('list', $list);
            return $this->fetch();
    }

    //逻辑删除
    public function delete() {
        $ids = input('ids/a');
var_dump($ids);exit;
        $result = CommunicatePosts::where('id', 'in', $ids)->update(['status' => -1]);
        if($result) {
            return $this->success('删除成功', url('Communication/showposts'));
        } elseif(!$result) {
            return $this->error('删除失败');
        }
    }

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

}