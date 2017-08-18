<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/8/16
 * Time: 下午2:59
 */
namespace app\index\controller;


use app\index\model\WechatUser;
use  app\index\model\PropertyServer;



class Property extends Base
{
    /*物业报修*/
    public function repair(){

        if (IS_POST){
          $property =new PropertyServer();
          $res=$property->save($_POST);
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

        //return  $this->fetch();
    }
    /*保洁服务*/
    public function clear(){
        if ($_POST){
            $property =new PropertyServer();
            $res=$property->save($_POST);
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
    /*物业报修记录*/
    public function repairRecord(){

        $list = PropertyServer::where(['type'=>['<',4]])->order('create_time desc')->paginate();
        int_to_string($list,['type'=>[1=>'空调报修',2=>"电梯报修",3=>"其他报修"]]);


        $this->assign('list',$list);

        return $this->fetch();
    }

    /*保洁记录*/

    public function clearRecord(){
        $list = PropertyServer::where(['type'=>['<',4]])->order('create_time desc')->paginate();
        int_to_string($list,['type'=>[4=>'保洁记录']]);

        $this->assign('list',$list);

        return $this->fetch();

    }
    /*下拉刷新*/
    public function listmore(){
        $type=input('type');
        $len = input('length');
        if ($type==1){

            $list = PropertyServer::where(['type'=>['<',4]])->order('create_time desc')->limit($len,6)->paginate();
            int_to_string($list,['type'=>[1=>'空调报修',2=>"电梯报修",3=>"其他报修"]]);
        }else{

            $list = PropertyServer::where(['type'=>4])->order('create_time desc')->limit($len,6)->paginate();
        }

        return $list;

    }




}