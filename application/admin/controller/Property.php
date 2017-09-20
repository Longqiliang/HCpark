<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/8/16
 * Time: 下午2:08
 */
namespace app\admin\controller;

use app\index\model\PropertyServer;

class Property extends Admin
{
    /*报修服务列表*/
    public function index(){

        $parkid =session("user_auth")['park_id'];
        $list=PropertyServer::where(['park_id'=> $parkid,'type'=>['<',4],'status'=>['in',[0,1,2]]])->order('create_time desc')->paginate();
        int_to_string($list,['type'=>[1=>'空调报修',2=>"电梯报修",3=>"其他报修"]]);
        int_to_string($list,['status'=>[0=>"进行中",1=>"已完成",2=>"审核失败"]]);
        $this->assign("list",$list);

        return $this->fetch();
    }
    /*保洁服务列表*/
    public function clear(){
        $parkid =session("user_auth")['park_id'];
        $list=PropertyServer::where(['park_id'=> $parkid,'type'=>4,'status'=>['in',[0,1,2]]])->order('id desc')->paginate();
        int_to_string($list,['type'=>[4=>'保洁记录']]);
        int_to_string($list,['status'=>[0=>"进行中",1=>"已完成",2=>"审核失败"]]);
        $this->assign("list",$list);

        return $this->fetch();



    }
    /*修改报修服务和保洁服务的状态值*/
    public  function changeStatus(){
        $id=input('id');
        $uid = input('del');
        $remark = input('check_remark');
        $res=PropertyServer::where('id',$id)->update(['status'=>$uid,'check_remark'=>$remark]);
        if ($uid == -1){
            $msg = "删除成功";
            $msgs = "删除失败";
        }else{
            $msg = "审核成功";
            $msgs = "审核失败";
        }
        if ($res){

            $this->success( $msg);
        }else{

            $this->error( $msgs);
        }

    }

    /*显示凭证*/
    public function showImage(){
        $id = input("id");
        $html="";
        $feepayment = PropertyServer::get($id);
        if ($feepayment['image']){
            $image = json_decode($feepayment['image']);

            foreach($image as $value){
                $html .= "<div class='col-md-4'><img class='front_cover_img' src='$value' style='width: 150px;height: 200px;'></div>";

            }
        }

        return $html;

    }





}