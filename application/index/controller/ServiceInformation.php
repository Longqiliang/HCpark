<?php
namespace app\index\controller;
use think\Controller;
use app\index\model\ServiceInformation as ServiceModel;
use app\index\model\Park;

class ServiceInformation extends Base
{
    /**服务信息**/
    /** 首页列表 **/
    public function index(){
        $parkid =session('park_id');
        $map=array(
            'park_id'=>$parkid,
            'status'=>1,
        );

        $list = ServiceModel::where($map)->order('create_time  desc')->limit(6)->select();
echo json_encode($list);exit;
        $this->assign('list',json_encode($list));
        return $this->fetch();
    }

    /*首页列表下拉刷新*/
    public function listManage(){
        $len = input("length");
        $parkid =session('park_id');
        $map=array(
            'park_id'=>$parkid,
            'status'=>1,
        );

        $list = ServiceModel::where($map)
            ->order("create_time desc")
            ->limit($len,6)
            ->select();

        if ($list){

            return json(['code' => 1, 'data' => json_encode($list)]);
        }else{

            return json(['code' => 0, 'msg' =>"没有更多内容了"]);
        }

    }

    /*详情页*/
    public function detail(){
        $id = input('id');
        $info = ServiceModel::get($id);
        $parkid =session('park_id');
        //发布园区
        $park=Park::where('id','eq',$parkid)->field('name')->find();
        //echo json_encode($info);exit;
        $this->assign('park', $park['name']);
        $this->assign('news', $info);
        return $this->fetch();
    }

}
