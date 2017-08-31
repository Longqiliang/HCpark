<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/17
 * Time: 9:33
 */
namespace app\admin\controller;

use app\common\model\BroadbandPhone as BroadbandModel;
use app\common\model\WechatUser;
use think\Db;
class BroadbandPhone extends Admin
{
    public function index()
    {


        $parkid =session("user_auth")['park_id'];
        $search = input('search');
        $map['b.status']  = ['neq',-1];
        if ($search != '') {
            $map['b.company'] = ['like','%'.$search.'%'];
        }
        $list = Db::table('tb_broadband_phone')
            ->alias('b')
            ->join('__WECHAT_USER__ w', 'b.user_id=w.userid')
            ->field('b.id,b.user_id,b.address,b.business,b.business_time,b.company,b.people,b.status,b.mobile,b.remark,b.create_time')
            ->where('w.park_id','eq',$parkid)
            ->where($map)
            ->order('create_time desc')
            ->paginate();


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
    public function complete() {
        $id = input('id');
        $result = BroadbandModel::where('id', 'in', $id)->update(['status' => 1]);
        if ($result){

            $this->success("已审核");
        }else{

            $this->error("未成功");
        }
    }

}