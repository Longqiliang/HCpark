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
use app\index\model\Park;

class EnterpriseRecruitment extends Base
{
    /**企业招聘**/
    /** 首页列表 **/
    public function index(){
        $parkid =session('park_id');
        $search = input('search');
        $map=array(
            'park_id'=>$parkid,
            'status'=>1,
        );
        if ($search != '') {
            $map['position'] = ['like','%'.$search.'%'];
        }

        $list = EnterpriseModel::where($map)->order('create_time  desc')->field('id,position,company,education,experience,number,wages')->limit(6)->select();

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

        $search = input('search');
        if ($search != '') {
            $map['position'] = ['like','%'.$search.'%'];
        }
        $list = EnterpriseModel::where($map)
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
        $info = EnterpriseModel::where('id',$id)->find();

        $this->assign('info', $info);
        return $this->fetch();
    }

}