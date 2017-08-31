<?php
namespace app\index\controller;
use think\Controller;
use wechat\TPWechat;
use wechat\TPQYWechat;
use think\Loader;
use app\common\model\CompanyContract;
class Partymanage extends Base
{
    /** 园区管理首页 **/
    public function index(){

        return $this->fetch();
    }

    /** 园区统计 **/
    public function statistics(){

        return $this->fetch();
    }
    /***合同管理*/
    public function manage(){
        $data[0] = CompanyContract::where(["park_id"=>session("park_id"),'type'=>1])->count();
        $data[1] = CompanyContract::where(["park_id"=>session("park_id"),'type'=>2])->count();
        $contract[0] = $data[0] + $data[1];
        $contract[1] = 100*$data[0]/($data[0] + $data[1]);
        $contract[2] = 100*$data[1]/($data[0] + $data[1]);

        return $this->fetch();
    }
    /*合同列表*/
    public function managelist(){
        $type = input("type");
        $list = CompanyContract::where(["park_id"=>session("park_id"),'type'=>$type])
                                    ->order("create_time desc")
                                    ->limit(6)
                                    ->select();

        $this->assign('list',$list);

        return $this->fetch();
    }
    /*合同下拉刷新*/
    public function listManage(){
        $type = input("type");
        $len = input("length");
        $list = CompanyContract::where(["park_id"=>session("park_id"),'type'=>$type])
            ->order("create_time desc")
            ->limit($len,6)
            ->select();
        if ($list){

            return json(['code' => 1, 'data' => json_encode($list)]);
        }else{

            return json(['code' => 0, 'msg' =>"没有更多内容了"]);
        }

    }
    /*合同详情*/
    public function manageDetail(){
        $id = input('id');
        $manageInfo = CompanyContract::get($id);
        foreach($manageInfo as $v){
            $v['img'] = unserialize($v['img']);

        }
        $info = [
            'extra'=>$manageInfo['remark'],
            'img'=>unserialize($manageInfo['img'])
        ];
        $this->assign('info', json_encode($info) );
    }

}
