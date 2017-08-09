<?php

namespace app\admin\controller;

use app\admin\model\ActionLog;
use app\admin\model\Action as ActionModel;
use app\admin\model\Member;

class Action extends Admin
{
    public function index() {
        $list = ActionModel::where('status', 'gt', -1)->order('id desc')->paginate();
        int_to_string($list);
        $this->assign('list', $list);

        // 记录当前列表页的cookie
        cookie('__forward__', $_SERVER['REQUEST_URI']);
        return $this->fetch();
    }
    /**
     * 行为日志列表
     */
    public function log(){
        $list = ActionLog::where('status', 'gt', -1)->order('id desc')->paginate();
        foreach ($list as &$value) {
            if(strtolower($value['model']) == 'anchor'){
                $anchor = Anchor::get($value['user_id']);
                $value['username'] = $anchor['nickname'];
            } elseif(strtolower($value['model']) == 'company'){
                $company = Company::get($value['user_id']);
                $value['username'] = $company['nickname'];
            } else {
                $member = Member::get($value['user_id']);
                $value['username'] = $member['nickname'];
            }
        }
        $this->assign('list', $list);

        return $this->fetch();
    }

    /**
     * 删除日志
     */
    public function remove(){
        if(ActionLog::destroy(input('ids/a'))) {
            $this->success('删除成功！');
        } else {
            $this->error('删除失败！');
        }
    }

    /**
     * 新增行为
     */
    public function add(){
        $this->assign('data',null);
        $this->display('editaction');
    }

    /**
     * 编辑行为
     */
    public function edit(){
        $data = ActionModel::get(input('id'));
        $this->assign('data',$data);

        return $this->fetch('editaction');
    }

}
