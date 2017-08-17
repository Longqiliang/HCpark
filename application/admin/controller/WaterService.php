<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/17
 * Time: 9:33
 */
namespace app\admin\controller;

use app\common\model\WaterService as WaterModel;

class WaterService extends Admin
{
    public function index()
    {
        $map = [
            'status'=> 1
        ];
        $search = input('search');
        if ($search != '') {
            $map['title'] = ['like','%'.$search.'%'];
        }
        $list = WaterModel::where($map)->order('id desc')->paginate();

        $this->assign('list',$list);
        return $this->fetch();
    }

//详情页
    public function detail(){
        $id=input('id');
        $result=WaterModel::where('id','eq',$id)->find();
        $this->assign('res',$result);
        return $this->fetch();
    }

//逻辑删除
    public function moveToTrash() {
        $ids = input('ids/a');
        $result = WaterModel::where('id', 'in', $ids)->update(['status' => -1]);
        if($result) {
            return $this->success('删除成功', url('WaterService/index'));
        } elseif(!$result) {
            return $this->error('删除失败');
        }
    }


}