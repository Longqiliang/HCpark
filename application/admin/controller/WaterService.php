<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/17
 * Time: 9:33
 */
namespace app\admin\controller;

use app\common\model\WaterService as WaterModel;
use app\common\model\WechatUser;
use think\Db;
class WaterService extends Admin
{
    public function index()
    {
        $parkid =session("user_auth")['park_id'];
        $search = input('search');
        $map['s.status']  = ['neq',-1];
        if ($search != '') {
            $map['s.name'] = ['like','%'.$search.'%'];
        }
        $list = Db::table('tb_water_service')
            ->alias('s')
            ->join('__WECHAT_USER__ w', 's.userid=w.userid')
            ->field('s.id,s.userid,s.name,s.mobile,s.address,s.number,s.create_time,s.status')
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

    //完成
    public function complete() {
        $id = input('id');
        $result = WaterModel::where('id', 'in', $id)->update(['status' => 1]);
        return $this->redirect('index');
    }



}