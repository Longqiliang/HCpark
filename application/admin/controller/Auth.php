<?php

namespace app\admin\controller;

use app\admin\model\AuthGroupAccess;
use app\admin\model\AuthRule;
use app\admin\model\AuthGroup;
use app\admin\model\Member;

class Auth extends Admin
{
    /**
     * 后台节点配置的url作为规则存入auth_rule
     * 执行新节点的插入,已有节点的更新,无效规则的删除三项任务
     */
    public function updateRules(){
        //需要新增的节点必然位于$nodes
        $nodes = $this->returnNodes(false);

        $map = array('module'=>'admin','type'=>array('in','1,2'));//status全部取出,以进行更新
        //需要更新和删除的节点必然位于$rules
        $rules = AuthRule::all(function($query) use($map) {
            $query->where($map);
        });

        //构建insert数据
        $data = array();//保存需要插入和更新的新节点
        foreach ($nodes as $value){
            $temp['name']   = $value['url'];
            $temp['title']  = $value['title'];
            $temp['module'] = 'admin';
            if($value['pid'] > 0){
                $temp['type'] = AuthRule::RULE_URL;
            }else{
                $temp['type'] = AuthRule::RULE_MAIN;
            }
            $temp['status'] = 1;
            $data[strtolower($temp['name'].$temp['module'].$temp['type'])] = $temp;//去除重复项
        }

        $update = array();//保存需要更新的节点
        $ids    = array();//保存需要删除的节点的id
        foreach ($rules as $index=>$rule){
            $key = strtolower($rule['name'].$rule['module'].$rule['type']);
            if ( isset($data[$key]) ) {//如果数据库中的规则与配置的节点匹配,说明是需要更新的节点
                $data[$key]['id'] = $rule['id'];//为需要更新的节点补充id值
                $update[] = $data[$key];
                unset($data[$key]);
                unset($rules[$index]);
                unset($rule['condition']);
                $diff[$rule['id']]=$rule;
            }elseif($rule['status']==1){
                $ids[] = $rule['id'];
            }
        }

        if(count($update)) {
            foreach ($update as $k=>$row){
                if ( $row!=$diff[$row['id']] ) {
                    AuthRule::where(array('id'=>$row['id']))->update($row);
                }
            }
        }

        if(count($ids)) {
            // 删除规则是否需要从每个用户组的访问授权表中移除该规则?
            AuthRule::where( array( 'id'=>array('IN',implode(',',$ids)) ) )->update(array('status'=>-1));
        }

        if(count($data)) {
            foreach ($data as $value) {
                AuthRule::create($value);
            }
        }
    }

    /**
     * 权限管理首页
     */
    public function index(){
        $list = AuthGroup::where('status', 'egt', 0)->order('id asc')->select();
        $list = int_to_string($list);
        $this->assign( '_list', $list );
        $this->assign( '_use_tip', true );

        return $this->fetch();
    }

    /**
     * 创建用户组
     */
    public function createGroup(){
        if(IS_POST) {
            $GroupModel = new AuthGroup();
            $result = $GroupModel->validate(true)->save($_POST);

            if($result) {
                $this->success("添加成功");
            } else {
                $this->error($GroupModel->getError());
            }
        } else {
            $auth = AuthGroup::get(input('id'));
            if ($auth) {
                $this->success('成功获取', '', $auth->toArray());
            } else {
                $this->error('获取失败');
            }
        }
    }

    /**
     * 编辑管理员用户组
     */
    public function editGroup(){
        $auth = new AuthGroup();
        $result = $auth->validate(true)->save($_POST, ['id'=>input('id')]);
        if($result) {
            $this->success("修改成功");
        } else {
            $this->error($auth->getError());
        }
    }

    /**
     * 访问授权页面
     */
    public function access(){
        $this->updateRules();

        $map = array('status'=>array('egt','0'),'module'=>'Admin','type'=>AuthGroup::TYPE_ADMIN);
        $auth_group = AuthGroup::where($map)->column("id,title,rules");
        $node_list  = $this->returnNodes();
        $map = array('module'=>'Admin','type'=>AuthRule::RULE_MAIN,'status'=>1);
        $main_rules = AuthRule::where($map)->column("name,id");
        $map = array('module'=>'Admin','type'=>AuthRule::RULE_URL,'status'=>1);
        $child_rules = AuthRule::where($map)->column("name,id");

        $this->assign('main_rules', $main_rules);
        $this->assign('auth_rules', $child_rules);
        $this->assign('node_list',  $node_list);
        $this->assign('auth_group', $auth_group);
        $this->assign('this_group', $auth_group[(int)input('group_id')]);

        return $this->fetch();
    }

    /**
     * 管理员用户组数据写入/更新
     */
    public function writeGroup() {
        $rules = input('rules/a');
        $data = array();
        if(isset($rules)) {
            sort($rules);
            $data['rules'] = implode(',' , array_unique($rules));
        }
        $data['module'] = 'admin';
        $data['type'] = AuthGroup::TYPE_ADMIN;

        $AuthGroupModel = new AuthGroup();
        $result = $AuthGroupModel->update($data, ['id'=> input('id')]);
        if($result) {
            $this->success('操作成功!');
        } else {
            $this->error($AuthGroupModel->getError());
        }
    }

    /**
     * 状态修改
     * @param null $method
     * @return array
     * @throws \think\Exception
     */
    public function changeStatus($method=null){
        if (empty(input('id'))) {
            $this->error('请选择要操作的数据!');
        }
        $result = false;
        switch (strtolower($method) ){
            case 'forbidgroup':
                $result = AuthGroup::where(['id'=>input('id')])->update(['status'=>0]);
                break;
            case 'resumegroup':
                $result = AuthGroup::where(['id'=>input('id')])->update(['status'=>1]);
                break;
            case 'deletegroup':
                $result = AuthGroup::where(['id'=>input('id')])->update(['status'=>-1]);
                break;
        }

        if($result) {
            $this->success('操作成功!');
        } else {
            $this->error('操作失败');
        }
    }

    /**
     * 用户组授权用户列表
     */
    public function user() {
        $list = AuthGroupAccess::where('group_id', input('group_id'))->paginate();
        $this->assign('list', $list);

        return $this->fetch();
    }

    /**
     * 将用户添加到用户组,入参uid,group_id
     */
    public function addToGroup(){
        $uid = input('id');
        $gid = input('group_id');
        if(empty($uid)){
            $this->error('参数有误');
        }

        if(is_numeric($uid)){
            if(is_administrator($uid)) {
                $this->error('该用户为超级管理员');
            }
            if(!Member::get($uid)) {
                $this->error('用户不存在');
            }
        }

        $access = AuthGroupAccess::where(['uid'=>$uid])->find();
        if(empty($access)) {
            $result = AuthGroupAccess::create(['uid' => $uid, 'group_id' => $gid]);
        } else {
            $result = AuthGroupAccess::update(['group_id'=> $gid], ['uid'=>$uid]);
        }
        if($result){
            $this->success('操作成功');
        } else {
            $this->error('操作失败');
        }
    }

    /**
     * 将用户从用户组中移除  入参:uid,group_id
     */
    public function removeFromGroup(){
        $uid = input('uid');
        $gid = input('group_id');
        if($uid == UID){
            $this->error('不允许解除自身授权');
        }
        if( empty($uid) || empty($gid) ){
            $this->error('参数有误');
        }

        $map = array('uid' => $uid, 'group_id'=>$gid );
        if (AuthGroupAccess::where($map)->delete()){
            $this->success('操作成功');
        }else{
            $this->error('操作失败');
        }
    }
}
