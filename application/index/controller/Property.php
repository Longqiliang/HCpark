<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/8/16
 * Time: 下午2:59
 */
namespace app\index\controller;


use app\index\model\WechatUser;
use app\common\model\CompanyServer;


class Property extends Base
{
    /*物业报修*/
    public function repair(){
        if ($_POST){
          $res=CompanyServer::save($_POST);
          if ($res){

              return $this->success("报修成功");
          }else{

              return $this->error("报修失败");
          }

        }
        $userid =session("userId");
        $userinfo=WechatUser::where(['userid'=>$userid])->find();
        $data =[
            'name'=>$userinfo['name'],
            'mobile'=>$userinfo['mobile']
        ];

        //dump($data);
        $this->assign('data',$data);
        return  $this->fetch();
    }
    /*保洁服务*/
    public function clear(){
        if ($_POST){
            $res=CompanyServer::save($_POST);
            if ($res){

                return $this->success("报修成功");
            }else{

                return $this->error("报修失败");
            }

        }
        $userid =session("userId");
        $userinfo=WechatUser::where(['userid'=>$userid])->find();
        $data =[
            'name'=>$userinfo['name'],
            'mobile'=>$userinfo['mobile']
        ];

        //dump($data);
        $this->assign('data',$data);
        return  $this->fetch();


    }



}