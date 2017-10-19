<?php
/**
 * Created by PhpStorm.
 * User: shen
 * Date: 2017/9/2
 * Time: 下午2:23
 */
namespace app\index\controller;
use think\Controller;
use app\index\model\EnterpriseRecruitment as EnterpriseModel;
use app\index\model\ServiceInformation as ServiceModel;
use app\index\model\Park;

class Talentservice extends Base
{
    /**企业招聘**/
    /** 企业招聘首页列表 **/
    public function index(){
        $parkid =session('park_id');
        $map=array(
            'status'=>1,
        );

        $list = EnterpriseModel::where($map)->order('create_time  desc')->field('id,position,company,education,experience,number,wages')->select();

        $this->assign('list',json_encode($list));
        return $this->fetch();
    }



    /*企业招聘详情页*/
    public function detail(){
        $id = input('id');
        $info = EnterpriseModel::where('id',$id)->find();
        $this->assign('info', json_encode($info));
        return $this->fetch();
    }


    /**服务信息**/
    /** 服务信息首页列表 **/
    public function serviceIndex(){
        $parkid =session('park_id');
        $map=array(
            'status'=>1,
        );

        $list = ServiceModel::where($map)->order('create_time  desc')->limit(6)->select();

        $this->assign('list',json_encode($list));
        return $this->fetch();
    }

    /*服务信息列表下拉刷新*/
    public function serviceList(){
        $len = input("length");
        $parkid =session('park_id');
        $map=array(
            'status'=>1,
        );

        $list = ServiceModel::where($map)
            ->order("create_time desc")
            ->limit($len,6)
            ->select();

        if ($list){

            return json(['code' => 1, 'data' => $list]);
        }else{

            return json(['code' => 0, 'msg' =>"没有更多内容了"]);
        }

    }

    /*服务信息详情页*/
    public function serviceDetail(){
        $id = input('id');
        $info = ServiceModel::get($id);
        $info['views']=$info['views']+1;
        $info->save();
        $parkid =session('park_id');
        //发布园区
        $park=Park::where('id','eq',$info['park_id'])->field('name')->find();

        $this->assign('park', $park['name']);
        $this->assign('news', $info);
        return $this->fetch();
    }


}