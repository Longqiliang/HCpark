<?php
/**
 * Created by PhpStorm.
 * User: Butaier
 * Date: 2017/8/14
 * Time: 17:55
 */
namespace app\index\controller;

use app\index\model\WechatUser;
use app\index\model\CompanyApplication;
use  app\index\model\WechatTag;
use  app\index\model\CompanyService;
//企业服务
class Service extends Base{


  //服务列表
    public function index() {
        $user_id =session('userId');
        $park_id=session('park_id');
         $user =new WechatUser();
        $app= new  CompanyApplication();
         $info=$user->where('userid',$user_id)->find();
         if($info['tagid']==1){

             $map=[
                 'park_id'=>3,
                 'type'=>0
             ];
             //物业服务
             $PropertyServices=$app->where($map)->select();
             $map['type']=1;
             //企业服务
             $CompanyService=$app->where($map)->select();
             $is_boss="yes";


         }else{
             //物业服务
             $serviceApps= $app->where('type',0)->select();
             $PropertyServices=array();
             foreach ($serviceApps as $value){

                 $parkid= json_decode($value['park_id']);

                 foreach($parkid as $value2){
                     if($value2==$park_id ){
                         array_push($PropertyServices,$value);
                     }
                 }

             }
             //企业服务
             $companyApps=$app->where('type',1)->select();
             $CompanyService=array();
             foreach ($serviceApps as $value){

                 $parkid= json_decode($value['park_id']);

                 foreach($parkid as $value2){
                     if($value2==$park_id ){
                         array_push($PropertyServices,$value);
                     }
                 }

             }


             $is_boss="no";





         }
        $this->assign('is_boss',$is_boss);
        $this->assign('propert',$PropertyServices);
        $this->assign('company',$CompanyService);
        return $this->fetch();

    }

    //选择服务
    public function  onCheck(){
        $path=input('path');
        $app_id=input('id');
        $user_id = session('userId');
        $UserModel = new  WechatUser();
        $user=$UserModel->where('userid',$user_id)->find();
        $info['name']=$user['name'];
        $info['mobile']=$user['mobile'];
        $info['company']=$user->departmentName->name;
        $info['app_id']=$app_id;
        $this->assign('info',$info);
        return $this->fetch($path);

    }


     //预约
     public  function  order(){
      $compantService = new CompanyService();
           $data=input('');
           $re =$this->_checkData($data);
           if(!$re){
             return $this->error('参数缺失');
            }
           $map=[
          'company'=>$data['company'],
          'people'=>$data['name'],
          'mobile'=>$data['moblie'],
          'app_id'=>$data['app_id'],
          'remark'=>$data['remark'],
          'user_id'=>session('userId'),
          'create_time'=>time(),
          'status'=>0
      ];
     $result = $compantService->save($map);
     if($result){
         return $this->success('提交成功');
     }else{

         return $this->error('提交失败');

     }
     }


public  function  _checkData($data){
         if(empty($data)){
             return false;
         }
         if(!isset($data['company'])||
             !isset($data['name'])||
             !isset($data['moblie'])||
             !isset($data['app_id'])
         ){

             return false;

         }

    if(empty($data['company'])||
        empty($data['name'])||
        empty($data['moblie'])||
        empty($data['app_id'])
    ){

        return false;

    }

      return true;



}




}