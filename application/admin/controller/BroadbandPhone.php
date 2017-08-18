<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/17
 * Time: 9:33
 */
namespace app\admin\controller;

use app\common\model\BroadbandPhone as BroadbandModel;

class BroadbandPhone extends Admin
{
    public function index()
    {
        $map['status']  = ['neq',-1];
        $search = input('search');
        if ($search != '') {
            $map['company'] = ['like','%'.$search.'%'];
        }
        $list = BroadbandModel::where($map)->order('id desc')->paginate();
        $this->assign('list',$list);
        return $this->fetch();
    }

//详情页
    public function detail(){
        $id=input('id');
        $result=BroadbandModel::where('id','eq',$id)->find();
        $this->assign('res',$result);
        return $this->fetch();
    }

//逻辑删除
    public function moveToTrash() {
        $ids = input('ids/a');
        $result = BroadbandModel::where('id', 'in', $ids)->update(['status' => -1]);
        if($result) {
            return $this->success('删除成功', url('BroadbandPhone/index'));
        } elseif(!$result) {
            return $this->error('删除失败');
        }
    }

    //启用
    public function disable() {
        $id = input('id');
        $result = BroadbandModel::where('id', 'in', $id)->update(['status' => 1]);
        return $this->redirect('index');
    }
    //禁用
    public function ban() {
        $id = input('id');
        $result = BroadbandModel::where('id', 'in', $id)->update(['status' => 0]);
        return $this->redirect('index');
    }


}