<?php
/**
 * Created by PhpStorm.
 * User: Butaier
 * Date: 2017/8/14
 * Time: 17:55
 */
namespace app\index\controller;
use app\common\model\CarparkRecord;
use  app\index\model\CompanyService;
use app\common\model\CarparkService;
use app\index\model\WechatUser;
use app\index\model\CompanyApplication;
use app\index\model\Park;
use  app\index\model\WechatTag;


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
             $PropertyServices=$app->where($map)->order('id asc')->select();
             $map['type']=1;
             //企业服务
             $CompanyService=$app->where($map)->order('id asc')->select();
             $is_boss=true;


         }else{
             //物业服务
             $serviceApps= $app->where('type',0)->order('id asc')->select();
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
             $companyApps=$app->where('type',1)->order('id asc')->select();
             $CompanyService=array();
             foreach ($serviceApps as $value){

                 $parkid= json_decode($value['park_id']);

                 foreach($parkid as $value2){
                     if($value2==$park_id ){
                         array_push($PropertyServices,$value);
                     }
                 }

             }


             $is_boss=false;





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
        $park_id = session('park_id');
        $UserModel = new  WechatUser();
        $Park = new Park();
        $CardparkService= new CarparkService();
        $info=[];
        switch ($app_id){
            //车卡
            case 6:
                $map=[
                    'user_id'=>$user_id,
                ];
                $map['park_card']=array('exp','is not null');
                $usercard =$CardparkService->where($map)->select();

                if(count($usercard)>0){

                    $info['type']="old";

                }else{

                    $info['type']="new";
                }


                break;


            //充电柱
            case 7:  break;


            default:
                $user=$UserModel->where('userid',$user_id)->find();
                $info['name']=$user['name'];
                $info['mobile']=$user['mobile'];
                $info['company']=$user->departmentName->name;
                $info['app_id']=$app_id;
                break;


        }
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
          'mobile'=>$data['mobile'],
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
             !isset($data['mobile'])||
             !isset($data['app_id'])
         ){

             return false;

         }

    if(empty($data['company'])||
        empty($data['name'])||
        empty($data['mobile'])||
        empty($data['app_id'])
    ){

        return false;

    }

      return true;



}


    public  function  NewCard(){
        $park_id =session('park_id');
        $Park = new Park();
        $park= $Park->where('id',$park_id)->find();
        //支付宝用户
        $data['ailpay_user']=$park['ailpay_user'];
        //银行用户
        $data['bank_user']=$park['bank_user'];
        //缴费支付宝账号
        $data['payment_alipay']=$park['payment_alipay'];
        //缴费支付宝账号
        $data['payment_bank']=$park['payment_bank'];
        //停车卡单价
        $data['carpark_price']=$park['carpark_price'];
        //车卡押金
        $data['carpark_deposit']=$park['carpark_deposit'];

        $this->assign('info',json_encode($data));

        return $this->fetch();
    }



    public  function  addNewCard(){

        $CarparkRecord = new CarparkRecord();
        $CardparkService= new CarparkService();
        if(IS_POST) {
            $id = session('userId');
            $data = input('');
            $service = [
                'name' => $data['name'],
                'mobile' => $data['mobile'],
                'people_card' => $data['people_card'],
                'car_card' => $data['car_card'],
                'user_id' => $id,
                'status' => 0
            ];

            $re = $CardparkService->save($service);
            $record=[
                'type'=>1,
                'aging'=>$data['aging'],
                'payment_voucher'=>$data['payment_voucher'],
                'create_time'=>time(),
                'carpark_id'=>$re,
                'status'=>0,
                'money'=>((int)$data['carpark_price']*(int)$data['aging'])+100,
            ];
            $re2=$CarparkRecord->save($record);
            if($re2){
                $this->success('成功');

            }else{
                $this->error("失败");
            }

        }else{
            $data=input('');
            $this->assign('data',$data);
            return $this->fetch();
        }

    }









    public  function  OldCard(){
        $user_id =session('userId');
        $park_id =session('park_id');
        $Park = new Park();
        $carCard =new CarparkService();
        $park= $Park->where('id',$park_id)->find();
        $map['user_id']=$user_id;
        $map['park_card']=array('exp','is not null');
        $cardinfo =$carCard->where($map)->select();
        //支付宝用户
        $data['ailpay_user']=$park['ailpay_user'];
        //银行用户
        $data['bank_user']=$park['bank_user'];
        //缴费支付宝账号
        $data['payment_alipay']=$park['payment_alipay'];
        //缴费支付宝账号
        $data['payment_bank']=$park['payment_bank'];
        //停车卡单价
        $data['carpark_price']=$park['carpark_price'];
        //车卡押金
        $data['carpark_deposit']=$park['carpark_deposit'];
        //用户停车卡信息
        $data['cardlist']=$cardinfo;

        $this->assign('data',$data);

        return $this->fetch();
    }



    public  function  keepOldCard(){

        $CarparkRecord = new CarparkRecord();
        $CardparkService= new CarparkService();
        if(IS_POST) {
            $id = session('userId');
            $data = input('');
            $service = [
                'name' => $data['name'],
                'mobile' => $data['mobile'],
                'people_card' => $data['people_card'],
                'car_card' => $data['car_card'],
                'user_id' => $id,
                'status' => 0
            ];

            $re = $CardparkService->save($service);
            $record=[
                'type'=>2,
                'aging'=>$data['aging'],
                'payment_voucher'=>$data['payment_voucher'],
                'create_time'=>time(),
                'carpark_id'=>$re,
                'status'=>0,
                'money'=>$data['money'],
            ];
            $re2=$CarparkRecord->save($record);
            if($re2){
                $this->success('成功');

            }else{
                $this->error("失败");
            }

        }else{

            return $this->fetch();
        }



    }
    //车卡记录
    public  function  carRecord(){
        $service =new CarparkService();
        $user_id= session('userId');

        $list=$service->where('user_id',$user_id)->select();
        $this->assign('list',$list);
        return $this->fetch();



    }





}