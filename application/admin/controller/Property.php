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
        $list=PropertyServer::where(['park_id'=> $parkid,'type'=>['<',4],'status'=>['in',[0,1]]])->paginate();
        int_to_string($list,['type'=>[1=>'空调报修',2=>"电梯报修",3=>"其他报修"]]);
        int_to_string($list,['status'=>[0=>"进行中",1=>"已完成"]]);
        $this->assign("list",$list);

        return $this->fetch();
    }
    /*保洁服务列表*/
    public function clear(){
        $parkid =session("user_auth")['park_id'];
        $list=PropertyServer::where(['park_id'=> $parkid,'type'=>4,'status'=>['in',[0,1]]])->paginate();
        int_to_string($list,['type'=>[4=>'保洁记录']]);
        int_to_string($list,['status'=>[0=>"进行中",1=>"已完成"]]);
        $this->assign("list",$list);

        return $this->fetch();



    }
    /*修改报修服务和保洁服务的状态值*/
    public  function changeStatus(){
        $id=input('id');
        $uid = input('del');
        if ($uid==1){
            $res=PropertyServer::where('id',$id)->update(['status'=>-1]);
            if ($res){

                $this->success("删除成功");
            }else{

                $this->error("删除失败");
            }
        }
        $res =PropertyServer::where('id',$id)->update(['status'=>1]);
        if ($res){

            $this->success("已审核");
        }else{

            $this->error("未成功");
        }

    }


}