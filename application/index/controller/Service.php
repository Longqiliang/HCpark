<?php
/**
 * Created by PhpStorm.
 * User: Butaier
 * Date: 2017/8/14
 * Time: 17:55
 */
namespace app\index\controller;
use app\common\model\FeePayment;
use app\index\model\CarparkRecord;
use  app\index\model\CompanyService;
use app\index\model\CarparkService;
use app\index\model\WaterService;
use app\index\model\WechatUser;
use app\index\model\CompanyApplication;
use app\index\model\Park;
use  app\index\model\WechatTag;
use  app\index\model\PropertyServer;
use app\index\model\WaterService as WaterModel;
use app\index\model\BroadbandPhone as BroadbandModel;
use  app\index\model\ElectricityRecord;
use  app\index\model\ElectricityService;
use  app\index\model\AdvertisingRecord;
use  app\index\model\AdvertisingService;
use  app\index\model\FunctionRoomRecord;
use  app\index\model\WechatDepartment;

use  app\index\model\LedRecord;
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
             $is_boss="yes";


         }else{
             //物业服务
             $serviceApps= $app->where('type',0)->order('id asc')->select();
             $PropertyServices=array();
             foreach ($serviceApps as $value){

                 $parkid= json_decode($value['park_id']);

                 foreach($parkid as $value2){
                     if($value2==$park_id ){
                         $value['park_id']=$park_id;
                         array_push($PropertyServices,$value);

                     }
                 }

             }
             //企业服务
             $companyApps=$app->where('type',1)->order('id asc')->select();
             $CompanyService=array();
             foreach ($companyApps as $value){

                 $parkid= json_decode($value['park_id']);

                 foreach($parkid as $value2){
                     if($value2==$park_id ){
                         $value['park_id']=$park_id;
                         array_push($CompanyService,$value);
                     }
                 }

             }
             $is_boss="no";
         }
        $this->assign('is_boss',$is_boss);
        $this->assign('propert',json_encode($PropertyServices));
        $this->assign('company',json_encode($CompanyService));
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
        $AdService = new AdvertisingService();
        $info=[];
        switch ($app_id){
            case 1:


                break;

            //物业报修
            case 2:
                $userid =session("userId");
                $parkid=session('park_id');
                $parkInfo=Park::where('id',$parkid)->find();
                $userinfo=WechatUser::where(['userid'=>$userid])->find();
                $info =[
                    'name'=>$userinfo['name'],
                    'mobile'=>$userinfo['mobile'],
                    'propretyMobile'=>$parkInfo['property_phone']
                ];
                break;
            //室内保洁
            case 4:
                $userid =session("userId");
                $parkid=session('park_id');
                $parkInfo=Park::where('id',$parkid)->find();
                $userinfo=WechatUser::where(['userid'=>$userid])->find();
                $info =[
                    'name'=>$userinfo['name'],
                    'mobile'=>$userinfo['mobile'],
                    'propretyMobile'=>$parkInfo['property_phone']
                ];
                break;
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
            case 7:
               $es = new ElectricityService();
              $map=[
                  'user_id'=>$user_id,
                  'electricity_id'=>array('exp','is not null')



              ];
               $is_new=$es->where($map)->select();
                if(count($is_new)>0){
                  $info['type']="old";

             }else{
                    $info['type']="new";
                }
                break;
           //公共场所
            case 8:
                $re = $AdService->where('park_id',$park_id)->select();
                $info['adlist']=$re;
                break;
            default:
                $user=$UserModel->where('userid',$user_id)->find();
                $info['name']=$user['name'];
                $info['mobile']=$user['mobile'];
                $info['company']=$user->departmentName->name;
                $info['app_id']=$app_id;
                break;


        }
        $info['app_id']=$app_id;
        $this->assign('info',json_encode($info));
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



    //新柱办理
    public function  newPillar(){
        $data['app_id']=input('app_id');
        $park_id =session('park_id');
        $Park = new Park();
        $park= $Park->where('id',$park_id)->find();
        //充电柱单价
        $data['charging_price']=$park['charging_price'];
        //充电柱押金
        $data['charging_deposit']=$park['charging_deposit'];
        $this->assign('data',json_encode($data));
        return $this->fetch();
    }



    //新柱提交
    public function addNewPillar()
    {
        $PillarRecord = new ElectricityRecord();
        $PillarService= new ElectricityService();

        $id = session('userId');
        $data = input('');
        $service = [
            'name' => $data['name'],
            'mobile' => $data['mobile'],
            'user_id' => $id,
            'status' => 0,
            'create_time'=>time()
        ];

        $re = $PillarService->save($service);

        $record=[
            'type'=>1,
            'aging'=>$data['aging'],
            'payment_voucher'=>json_encode($data['payment_voucher']),
            'create_time'=>time(),
            'service_id'=>$PillarService->id,
            'status'=>0,
            'money'=>((int)$data['charging_price']*(int)$data['aging'])+(int)$data['charging_deposit'],
        ];
        $re2=$PillarRecord->save($record);
        if($re2){
            $this->success('成功'.json_encode($PillarService->id));

        }else{
            $this->error("失败");
        }

    }



    //旧柱办理
    public function  oldPillar(){
        $data['app_id']=input('app_id');
        $user_id =session('userId');
        $park_id =session('park_id');
        $Park = new Park();
        $pillar =new ElectricityService();
        $park= $Park->where('id',$park_id)->find();
        $map['user_id']=$user_id;
        $map['electricity_id']=array('exp','is not null');
        $cardinfo =$pillar->where($map)->select();
        //充电柱单价
        $data['charging_price']=$park['charging_price'];
        //充电柱押金
        $data['charging_deposit']=$park['charging_deposit'];
        //用户停车卡信息
        $data['cardlist']=$cardinfo;
        $this->assign('data',json_encode($data));
        return $this->fetch();
    }



    //旧柱提交
    public function addOldPillar()
    {
        $er = new ElectricityRecord();

        $data = input('');
        $record=[
            'type'=>2,
            'aging'=>$data['aging'],
            'payment_voucher'=>json_encode($data['payment_voucher']),
            'create_time'=>time(),
            'service_id'=>$data['id'],
            'status'=>0,
            'money'=>((int)$data['charging_price']*(int)$data['aging']),
        ];
        $re2=$er->save($record);
        if($re2){
            $this->success('成功');

        }else{
            $this->error("失败");
        }
    }

    //充电柱记录
    public  function  pillarRecord(){

        $service =new ElectricityService;
        $user_id= session('userId');
        $map=[
            'user_id'=>$user_id,
            'status'=>array('neq',-1)
        ];
        $list=$service->where($map)->select();
        $record=array();
        foreach ($list as $value){
            array_push($record,$value->findRecord);
        }
        int_to_string($record,array('type'=>array(1=>'新柱办理',2=>'旧柱办理'),'status'=>array(0=>'审核中',  1=>'审核通过', 2=>'审核失败'  )));
        $res=array();
        foreach ($record as $k=>$v){
            foreach ($v as $val){
                $res[$k]['name']=$val['type']==1?'新柱办理':"旧柱续费";
                $res[$k]['pay']=$val['money'];
                $res[$k]['time']=$val['create_time'];
                $res[$k]['status']=$val['status'];
                $res[$k]['id']=$val['id'];
            }
        }

        return $res;
    }



    //新车卡主页
    public  function  newCard(){
        $data['app_id']=input('app_id');
        $park_id =session('park_id');
        $Park = new Park();
        $park= $Park->where('id',$park_id)->find();
        //停车卡单价
        $data['carpark_price']=$park['carpark_price'];
        //车卡押金
        $data['carpark_deposit']=$park['carpark_deposit'];
        $this->assign('info',json_encode($data));

        return $this->fetch();
    }

   //新车卡，下一步
    public  function  nextNewCard(){

        $data=input('');
        $this->assign('data',json_encode($data));
        return $this->fetch('payment');

    }



    //凭证提交公共方法
    public function  payment(){
        $data = input('');
        $park_id =session('park_id');
        $cp=new CompanyApplication();
        $Park = new Park();
        $park= $Park->where('id',$park_id)->find();
        $CA=$cp->where('app_id',$data['app_id'])->find();
        //支付宝用户
        $data['ailpay_user']=$CA['has_alipay']==1?$park['ailpay_user']:"";
        //银行用户
        $data['bank_user']=$CA['has_bank']==1?$park['bank_user']:"";
        //缴费支付宝账号
        $data['payment_alipay']=$CA['has_alipay']==1?$park['payment_alipay']:"";
        //缴费支付宝账号
        $data['payment_bank']=$CA['has_bank']==1?$park['payment_bank']:"";
        $this->assign('data',json_encode($data));
        return $this->fetch();
    }


    //提交新卡
    public  function  addNewCard(){

        $CarparkRecord = new CarparkRecord();
        $CardparkService= new CarparkService();

            $id = session('userId');
            $data = input('');
        $p_v=array();
        $file = new File();
        foreach ($data['payment_voucher'] as $value){
            $info = $file->uploadPicture();
            $a['picture']=$value;
            array_push($p_v,$info);
            }
        $service = [
                'name' => $data['name'],
                'mobile' => $data['mobile'],
                'people_card' => $data['people_card'],
                'car_card' => $data['car_card'],
                'user_id' => $id,
                'status' => 0,
                'create_time'=>time()
            ];
            $re = $CardparkService->save($service);

            $record=[
                'type'=>1,
                'aging'=>$data['aging'],
                'payment_voucher'=>json_encode($data['payment_voucher']),
                'create_time'=>time(),
                'carpark_id'=>$CardparkService->id,
                'status'=>0,
                'money'=>((int)$data['carpark_price']*(int)$data['aging'])+(int)$data['carpark_deposit'],
            ];
            $re2=$CarparkRecord->save($record);
            if($re2){
                $msg="您的缴费信息正在核对中;核对完成后,将在个人中心中予以反馈;请耐心等待,确认成功后;请您在2小时内到海创大厦A座201领取车卡";
                $this->success('成功',"",$msg);

            }else{
                $this->error("失败");
            }
    }



    //旧卡首页
    public  function  oldCard(){
        $user_id =session('userId');
        $park_id =session('park_id');
        $data['app_id']=input('app_id');
        $Park = new Park();
        $carCard =new CarparkService();
        $park= $Park->where('id',$park_id)->find();
        $map['user_id']=$user_id;
        $map['park_card']=array('exp','is not null');
        $cardinfo =$carCard->where($map)->select();
        //停车卡单价
        $data['carpark_price']=$park['carpark_price'];
        //车卡押金
        $data['carpark_deposit']=$park['carpark_deposit'];
        //用户停车卡信息
        $data['cardlist']=$cardinfo;
        $this->assign('data',json_encode($data));
        return $this->fetch();
    }


    //旧卡续费（上传凭证）
    public  function  keepOldCard(){
        $CarparkRecord = new CarparkRecord();
        $CardparkService= new CarparkService();
            $id = session('userId');
            $data = input('');
            $record=[
                'type'=>2,
                'aging'=>$data['aging'],
                'payment_voucher'=>json_encode($data['payment_voucher']),
                'create_time'=>time(),
                'carpark_id'=>$data['id'],
                'status'=>0,
                'money'=>((int)$data['carpark_price']*(int)$data['aging']),
            ];
            $re2=$CarparkRecord->save($record);
            if($re2){
                $msg="您的缴费信息正在核对中;核对完成后,将在个人中心中予以反馈";
                $this->success('成功',"",$msg);

            }else{
                $this->error("失败");
            }
    }




    //车卡记录
    public  function  carRecord(){
        $service =new CarparkRecord();
        $map=[
            'status'=>array('neq',-1)
        ];
        $list=$service->where($map)->field('id,type,money,status,create_time')->select();

        foreach ($list as $v){
            $v['name']=$v['type']==1?'新卡办理':"旧卡续费";
            $v['pay']=$v['money'];
            $v['time']=$v['create_time'];
        }
        return $list;
    }




    //大厅广告位预约
    public function advertise(){
        $user_id = session('userId');
        $adService=new AdvertisingService();
        $adRecord = new AdvertisingRecord();
        //取消超时没有上传凭证的预约信息
        $nowtime=time()-60;
        $map=[
            'status'=>1,
            'create_time'=>array('lt',$nowtime)
        ];

        $out_date = $adRecord->where($map)->select();

        foreach ($out_date as $value){
            $value['status']=0;
            $value->save();
        }
        /* **************************************/

        //今天结束时间
        $endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
        //本月结束时间
        $endThismonth=mktime(23,59,59,date('m'),date('t'),date('Y'));
       unset($map['create_time']);
        $map['order_time']=array('between',array($endToday,$endThismonth));
        $map['service_id']=1;
        $map['status']=array('neq',0);
        //所有被选中的和预约成功的
        $list =$adRecord->where($map)->select();
        //该用户自己选中的（当月）
        $map['status']=array('eq',1);
        $map['create_user']=$user_id;
        $user_select = $adRecord->where($map)->select();
        //该用户自己选中的（全部）
        unset($map['order_time']);
        $user_allselect = $adRecord->where($map)->select();
        $user_allcheck=array();
        foreach ($user_allselect as $value){
            array_push($user_allcheck,$value['order_time']*1000);
        }
        //两者差值
        $reult = array_diff($list,$user_select);

        $service =$adService->where('id',1)->find();
        $this->assign('price',$service['price']);
        $data=array();
        foreach ($reult as $value){
            array_push($data,$value['order_time']*1000);
        }

        $this->assign('record',json_encode($data));
        $this->assign('user_check',json_encode($user_allcheck));
        $this->assign('app_id',input('app_id'));
        return $this->fetch();
    }



    //大厅广告位（下一步）
    public  function  nextAdvertise(){

           $data = input('');
           $user_id = session('userId');
           $ad = new AdvertisingRecord();
           $record = array();
           $creat_time = time();
           $is_select = array();
           foreach ($data['order_times'] as $value) {
              $map=['order_time'=> $value/1000,
               'status' => array('neq', 0),
                  'create_user'=>array('neq',$user_id)
                  ];

               $is = $ad->where($map)->find();

               if ($is) {
                   array_push($is_select, $is['order_time']*1000);
               } else {
                   $info = [
                       'create_user' => $user_id,
                       'service_id' => 1,
                       'order_time' => $value/1000,
                       'create_time' => $creat_time,
                       'status' => 1
                   ];
                   array_push($record, $info);
               }
           }
           if(count($is_select)>0){
               $data['no_save'] = json_encode($is_select);
               return   json_encode($data);
           }
        $map2 = [
            'create_user' => $user_id,
            'status' => 1
        ];
           $de = $ad->where($map2)->delete();
           $re = $ad->saveAll($record);

           $data['no_save'] = json_encode($is_select);


        return   json_encode($data);
    }





    //大厅广告位（提交）
    public  function  submitAdvertise(){
        $ad =new AdvertisingRecord();
        $user_id =session('userId');
        $data=input('');

        $map=[
            'create_user'=>$user_id,
             'status'=>1
        ];
        $record =$ad->where($map)->select();
        if(count($record)>0){
        foreach ($record as $value){
            $value['payment_voucher']=json_encode($data['payment_voucher']);
            $value['status']=2;
            $value->save();
        }
            $msg="您的缴费信息正在核对中;核对完成后，将在个人中心中予以反馈;请耐心等待";
            return  $this->success('成功',"",$msg);
        }
        else {
            return $this->error('超时');
        }}




    //大厅广告位月份切换
    public  function   changeMonth(){
        $adRecord = new AdvertisingRecord();
        //数字（几月）
        $month=input('month');


        $beginThismonth=mktime(0,0,0,$month,1,date('Y'));

        $endThismonth=mktime(23,59,59,$month,date('t'),date('Y'));
        $map['order_time']=array('between',array($beginThismonth,$endThismonth));
        $map['service_id']=1;
        $map['status']=array('eq',2);
        $list =$adRecord->where($map)->select();
        $reult =array();
        foreach ($list as $value){

            array_push($reult,$value['order_time']*1000);

        }

        return json_encode($reult);
    }


        //多功能厅
        public function multifunction(){
            $adService =new  AdvertisingService();
            $FunctionRoomRecord = new FunctionRoomRecord();
            $user_id = session('userId');
            //取消超时没有上传凭证的预约信息
            $nowtime=time()-60;
            $map=[
                'status'=>1,
                'create_time'=>array('lt',$nowtime)
            ];

            $out_date = $FunctionRoomRecord->where($map)->select();

            foreach ($out_date as $value){
                $value['status']=0;
                $value->save();
            }
            /* **************************************/
            //从今天到第七天结束的时间戳数组
            $weeks=array();
            for($i=1;$i<8;$i++){
                $days=array();
                $time=mktime(0,0,0,date('m'),date('d')+$i,date('Y'))-1;
                $map=['order_time'=>$time,'status'=>array('neq',0)];
                $re =$FunctionRoomRecord->where($map)->select();
                if($re){
                    foreach ($re as $value){
                   $days['day']=$time*1000;
                   //是当前用户
                   if($value['create_user']==$user_id){
                        //选中未付款
                        if($value['status']==1){
                            //上午
                            if($value['date_type']==1){
                                $days['amCheck']="yes";
                            }
                            //下午
                            else{
                                $days['pmCheck']="yes";
                            }
                        }
                        //已付款
                        else{
                             //上午
                            if($value['date_type']==1){
                                $days['amBespeak']="yes";
                            }
                            //下午
                            else{
                                $days['pmBespeak']="yes";
                            }
                        }
                    }
                    //不是当前用户
                    else{
                            //上午
                            if($value['date_type']==1){
                                $days['amBespeak']="yes";
                            }
                            //下午
                            else{
                                $days['pmBespeak']="yes";
                            }
                    }
                    }

                    $days['amBespeak']=isset($days['amBespeak'])?$days['amBespeak']:"no";
                    $days['pmBespeak']=isset($days['pmBespeak'])?$days['pmBespeak']:"no";
                    $days['amCheck']=isset($days['amCheck'])?$days['amCheck']:"no";
                    $days['pmCheck']=isset($days['pmCheck'])?$days['pmCheck']:"no";


                }
                else{
                    $days['day']=$time*1000;
                    $days['amBespeak']="no";
                    $days['pmBespeak']="no";
                    $days['amCheck']="no";
                    $days['pmCheck']="no";
                }
                 array_push($weeks,$days);
            }
            $this->assign('data',json_encode($weeks));
            $this->assign('app_id',input('app_id'));
            return $this->fetch();
        }
    //多功能厅预定先生成数据
    public function nextFunctionRoom(){
        $data = input('');
        $user_id = session('userId');
        $frr = new FunctionRoomRecord();
        $record = array();
        $create_time = time();
        $map = [
            'create_user' => $user_id,
            'status' => 1
        ];
        $is_select = array();
        foreach ($data as $value) {
           if($value['amCheck']=="yes") {
               $map = ['order_time' => $value['day'] / 1000,
                   'status' => array('neq', 0),
                   'create_user' => array('neq', $user_id),
                   'date_type' => 1
               ];
               $is1 = $frr->where($map)->find();
               if ($is1) {
                   $map=[
                       'day'=>$is1['order_time']*1000,
                       'type'=>'am'
                   ];
                   array_push($is_select, $map);
               }else{

                   $info = [
                       'create_user' => $user_id,
                       'service_id' => 2,
                       'order_time' => $value['day']/1000,
                       'create_time' => $create_time,
                       'status' => 1,
                       'date_type'=>1

                   ];
                   array_push($record, $info);

               }

           }
            if($value['pmCheck']=="yes") {
                $map = ['order_time' => $value['day'] / 1000,
                    'status' => array('neq', 0),
                    'create_user' => array('neq', $user_id),
                    'date_type' => 2
                ];
                $is2 = $frr->where($map)->find();
                if($is2){
                    $map=[
                        'day'=>$is1['order_time']*1000,
                        'type'=>'pm'
                    ];
                    array_push($is_select, $map);

                }else{
                    $info = [
                        'create_user' => $user_id,
                        'service_id' => 2,
                        'order_time' => $value['day']/1000,
                        'create_time' => $create_time,
                        'status' => 1,
                        'date_type'=>2

                    ];
                    array_push($record, $info);

                }
           }

        }
        if(count($is_select)>0){
            $data['no_save'] = json_encode($is_select);
            return   json_encode($data);
        }
        $map2=[
            'status'=>array('eq',1),
            'create_user'=>$user_id
        ];

        $de = $frr->where($map2)->delete();
        $re = $frr->saveAll($record);
        $data['no_save'] = json_encode($is_select);
        return   json_encode($data);
    }

    //二楼多功能厅（提交）
    public  function  submitFunctionRoom(){
        $ad =new FunctionRoomRecord();
        $user_id =session('userId');
        $data=input('');
        $map=[
            'create_user'=>$user_id,
            'status'=>1
        ];
        $record =$ad->where($map)->select();
        if(count($record)>0){
            foreach ($record as $value){
                $value['payment_voucher']=json_encode($data['payment_voucher']);
                $value['status']=2;
                $value->save();
            }
            $msg="您的缴费信息正在核对中;核对完成后，将在个人中心中予以反馈;请耐心等待";
            return  $this->success('成功',"",$msg);
        }
        else {
            return $this->error('超时');
        }}
   //led 灯服务
   public  function  led(){
     $user_id = session('userId');
        $led = new LedRecord();
       //取消超时没有上传凭证的预约信息
       $nowtime=time()-600;
       $map=[
           'status'=>1,
           'create_time'=>array('lt',$nowtime)
       ];

       $out_date = $led->where($map)->select();

       foreach ($out_date as $value){
           $value['status']=0;
           $value->save();
       }
       /* **************************************/

        //明天的时间
       $Today=mktime(8,0,0,date('m'),date('d')+1,date('Y'));
        $map=[
         'create_user'=>$user_id,
         'status'=>1,

        ];
       //用户约成功的
       $all_check2=array();
       $map2=[
           'create_user'=>$user_id,
           'status'=>2,
       ];
       $usersuccess=$led->where($map2)->select();
       foreach ($usersuccess as $value){
           $data=[

               'interval'=>$value['date_type'],
               'day'=>$Today*1000
           ];
           array_push($all_check2,$data);
       }

      //用户所有选中的
      $user_check=$led->where($map)->select();
      $user_check2=array();
      foreach ($user_check as $value){
          $info=[
              'day'=>$value['order_time']*1000,

              'interval'=>$value['date_type']
          ];
          array_push($user_check2,$info);
      }

      $map['create_user']=array('neq',$user_id);
      $map['status']=array('neq',0);
      $map['order_time']=$Today;
       //今天已选的
       $all_check=$led->where($map)->select();
       foreach ($all_check as $value){
        $data=[

            'interval'=>$value['date_type'],
            'day'=>$Today*1000
        ];
        array_push($all_check2,$data);
       }


       echo json_encode($all_check2);
        $this->assign('app_id',input('app_id'));
        $this->assign('other',json_encode($all_check2));
        $this->assign('user',json_encode($user_check2));
      return $this->fetch();
   }
   //led灯下一步
    public  function  nextLed(){
        $data = input('');
        $user_id = session('userId');

        $ad = new LedRecord();
        $record = array();
        $creat_time = time();
        $is_select = array();
        foreach ($data as $value) {
            $map=['order_time'=> $value['day']/1000,
                'status' => array('neq', 0),
                'create_user'=>array('neq',$user_id),
                'date_type'=>$value['interval']

            ];

            $is = $ad->where($map)->find();

            if ($is) {
                array_push($is_select, $is['order_time']);
            } else {
                $info = [
                    'create_user' => $user_id,
                    'service_id' => 3,
                    'order_time' => $value['day']/1000,
                    'create_time' => $creat_time,
                    'status' => 1,
                     'date_type'=>$value['interval']
                ];
                array_push($record, $info);
            }
        }
        if(count($is_select)>0){
            $data['no_save'] = json_encode($is_select);
            return   json_encode($data);
        }
        $map2 = [
            'create_user' => $user_id,
            'status' => 1
        ];
        $de = $ad->where($map2)->delete();
        $re = $ad->saveAll($record);
        $data['no_save'] = json_encode($is_select);

        return   json_encode($data);
    }
// led灯的日期切换
 public function changeLed(){
     $data2=input('day');

     $user_id = session('userId');
     $led = new LedRecord();
     $map['create_user']=array('neq',$user_id);
     $map['status']=array('neq',0);
     $map['order_time']=$data2/1000;


     //用户约成功的
     $all_check2=array();
     $map2=[
         'create_user'=>$user_id,
         'status'=>2,
     ];
     $usersuccess=$led->where($map2)->select();
     foreach ($usersuccess as $value){
         $data=[
             'interval'=>$value['date_type'],
             'day'=>$value['order_time']*1000
         ];
         array_push($all_check2,$data);
     }
     //今天已选的
     $all_check=$led->where($map)->select();
     foreach ($all_check as $value){
         $data=[
             'interval'=>$value['date_type'],
              'day'=>$data2
         ];
         array_push($all_check2,$data);
     }




     return json_encode($all_check2);


   }
   //Led灯提交
    public  function  submitLed(){
        $ad =new LedRecord();
        $user_id =session('userId');
        $data=input('');
        $map=[
            'create_user'=>$user_id,
            'status'=>1
        ];
        $record =$ad->where($map)->select();
        if(count($record)>0){
            foreach ($record as $value){
                $value['payment_voucher']=json_encode($data['payment_voucher']);
                $value['status']=2;
                $value->save();
            }
            $msg="您的缴费信息正在核对中;核对完成后，将在个人中心中予以反馈;请耐心等待";
            return  $this->success('成功',"",$msg);
        }
        else {
            return $this->error('超时');
        }}




    //公共场所全部广告记录分类
    public  function  record(){
        $type=input('t');
        $user_id=session('userId');
        $service = new AdvertisingService();
        $ad=new AdvertisingRecord();
        $fs = new FunctionRoomRecord();
        $led = new LedRecord();
        $data=array();
        switch ($type){
          //大厅广告记录
          case 1:
              $data=array();
              $time=array();
              $create_time=array();
              $serviceInfo =$service->where('id',1)->find();
              $list= $ad->where('create_user',$user_id)->order('create_time desc')->select();
              //所有的创建时间
              foreach ($list as $l){
              array_push($create_time,$l['create_time']);
              }

              //数组去重
              $time = array_values(array_unique ($create_time));

              foreach ($time as $onetime){
               $map =array();
                  foreach ($list as  $info){
                   if($info['create_time']==$onetime){
                       array_push($map,$info);
                   }
                  }
                  $re=[
                     'create_time'=>strtotime($onetime)*1000,
                      'price'=>count($map)*$serviceInfo['price']
                  ];
                   $re['day']="";

                  foreach ($map as  $value){
                      $re['day'].=date('Y-m-d',$value['order_time'])." ";
                   }
                   if($map[0]['status']==0){

                       $re['status']="被取消";
                   }else if($map[0]['status']==1){
                       $re['status']="还未上传凭证";

                   }else{
                       $re['status']="预约成功";
                   }

                array_push($data,$re);

              }
              break;

          //二楼多功能厅
          case 2:
              $data=array();
              $time=array();
              $create_time=array();
              $serviceInfo =$service->where('id',2)->find();
              $list= $fs->where('create_user',$user_id)->order('create_time desc')->select();
              //所有的创建时间
              foreach ($list as $l){
                  array_push($create_time,$l['create_time']);
              }
              //数组去重
              $time = array_values(array_unique ($create_time));

              foreach ($time as $onetime){
                  $map =array();
                  foreach ($list as  $info){
                      if($info['create_time']==$onetime){
                          array_push($map,$info);
                      }
                  }
                  $re=[
                      'create_time'=>strtotime($onetime)*1000,
                      'price'=>count($map)*$serviceInfo['price']
                  ];
                  $re['day']="";
                  //这个map为这一条记录的所有用户选中预约天数（因为要考虑上下午，还要按天分）
                  $map_time=array();
                  foreach ($map as $m){
                      array_push($map_time,$m['order_time']);
                  }
                  $mtime_list = array_values(array_unique ($map_time));

                  foreach ($mtime_list as $value){
                      $re['day'].=date('Y-m-d',$value);
                      foreach($map as $value2){
                      if($value ==$value2['order_time']){

                         if($value2['date_type']==1){
                             $re['day'].="上午 ";
                         }
                          elseif($value2['date_type']==2){
                              $re['day'].="下午 " ;
                          }
                      }
                    }

                  }
                  if($map[0]['status']==0){

                      $re['status']="被取消";
                  }else if($map[0]['status']==1){
                      $re['status']="还未上传凭证";

                  }else{
                      $re['status']="预约成功";
                  }

                  array_push($data,$re);

              }

              break;
          //大堂led灯
          case 3:
              $data=array();
              $time=array();
              $create_time=array();
              $serviceInfo =$service->where('id',3)->find();
              $list= $led->where('create_user',$user_id)->order('create_time desc')->select();
              //所有的创建时间
              foreach ($list as $l){
                  array_push($create_time,$l['create_time']);
              }
              //数组去重
              $time = array_values(array_unique ($create_time));

              foreach ($time as $onetime){
                  $map =array();
                  foreach ($list as  $info){
                      if($info['create_time']==$onetime){
                          array_push($map,$info);
                      }
                  }
                  $re=[
                      'create_time'=>strtotime($onetime)*1000,
                      'price'=>count($map)*$serviceInfo['price']
                  ];
                  $re['day']="";
                  //这个map为这一条记录的所有用户选中预约天数（因为要考虑上下午，还要按天分）
                  $map_time=array();
                  foreach ($map as $m){
                      array_push($map_time,$m['order_time']);
                  }
                  $mtime_list = array_values(array_unique ($map_time));

                  foreach ($mtime_list as $value){
                      $re['day'].=date('Y-m-d',$value)."| ";
                      foreach($map as $value2){
                          if($value ==$value2['order_time']){
                            switch($value2['date_type']){
                                case 1:$re['day'].="9:00-10:00 ";break;
                                case 2:$re['day'].="10:00-11:00 ";break;
                                case 3:$re['day'].="11:00-12:00 ";break;
                                case 4:$re['day'].="12:00-13:00 ";break;
                                case 5:$re['day'].="13:00-14:00 ";break;
                                case 6:$re['day'].="14:00-15:00 ";break;
                                case 7:$re['day'].="15:00-16:00 ";break;
                                case 8:$re['day'].="16:00-17:00 ";break;
                                case 9:$re['day'].="17:00-18:00 ";break;
                            }

                          }
                      }

                  }
                  if($map[0]['status']==0){

                      $re['status']="被取消";
                  }else if($map[0]['status']==1){
                      $re['status']="还未上传凭证";

                  }else{
                      $re['status']="预约成功";
                  }

                  array_push($data,$re);

              }

              break;

      }
     $this->assign('data',json_encode($data));
     return $this->fetch();


    }



    /*物业报修*/
    public function repair(){
        $userid =session("userId");
        $parkid=session('park_id');
        $property =new PropertyServer();
        $data = input('post.');
        $data['create_time']=time();
        $data['user_id']=$userid;
        $data['park_id']=$parkid;
        $data["image"] = json_encode($data["payment_voucher"]);
        $res=$property->allowField(true)->save($data);

        if ($res){

            return $this->success("预约成功",""," 请您在2小时内前往海创大厦A座2楼201；
                    进行费用缴纳和相关手续办理");
        }else{

            return $this->error("报修失败");
        }


    }
    /*保洁服务*/
    public function clear(){
        $userid =session("userId");
        $parkid=session('park_id');
        $data = input('post.');
        $time = date("w",strtotime(input("dateStr")));
        if ($time ==6 ||$time ==0){
            return $this->error("请在工作日预约");
        }
        $data['clear_time']=strtotime(input("dateStr"));
        $data['user_id']=$userid;
        $data['park_id']=$parkid;
        $data["image"] = json_encode($data["payment_voucher"]);

        $property =new PropertyServer();

        $res=$property->allowField(true)->save($data);
        if ($res){

            return $this->success("预约成功",""," 请您在2小时内前往海创大厦A座2楼201；
                    进行费用缴纳和相关手续办理");
        }else{

            return $this->error("报修失败");
        }


    }
    /*物业报修记录*/
    public function repairRecord(){
        $types=[1=>'空调报修',2=>"电梯报修",3=>"其他报修"];
        $list = PropertyServer::where(['type'=>['<',4],'status'=>['>=',0]])->order('id desc')->paginate();
        foreach($list as $k=>$v){
            $info[$k]=[
                'id'=>$v['id'],
                'type'=>$types[$v['type']],
                'time'=>date('Y-m-d',$v['clear_time']),
                'status'=>$v['status'],
            ];
        }

        return  $info;

    }

    /*保洁服务记录*/

    public function clearRecord(){
        $list = PropertyServer::where(['type'=>['<',4],'status'=>['>=',0]])->order('clear_time desc')->paginate();
        foreach($list as $k=>$v){
            $info[$k]=[
                'id'=>$v['id'],
                'type'=>"保洁服务",
                'time'=> date("Y-m-d", $v['clear_time']),
                'name'=>$v['address'],
            ];
        }

        return  $info;

    }
    /*物业保洁下拉刷新*/
    public function listmore(){
        $type=input('type');
        $len = input('length');
        if ($type==1){

            $list = PropertyServer::where(['type'=>['<',4],'status'=>['in',[0,1]]])->order('create_time desc')->limit($len,6)->paginate();
            int_to_string($list,['type'=>[1=>'空调报修',2=>"电梯报修",3=>"其他报修"]]);
        }else{

            $list = PropertyServer::where(['type'=>4,'status'=>['in',[0,1]]])->order('create_time desc')->limit($len,6)->paginate();
        }

        return $list;

    }

    //饮水服务
    public function waterService()
    {
        $data=input('post.');
        $waterModel = new WaterModel;
        $data['userid']=session('userId');
        $result = $waterModel->allowField(true)->validate(true)->save($data);
        if ($result) {
            //预约成功
            return $this->success("预约成功");
        } else {
            return $this->error($waterModel->getError());
        }

    }

    //饮水服务列表页
    public function waterList(){
        $userid = session('userId');
  //      echo  $userid;
        $map = [
            'status'=> array('neq',-1),
            'userid'=>$userid,
        ];
        $list = WaterModel::where($map)->order('id desc')->paginate();

        foreach($list as $k=>$v){
            $info[$k]=[
                'id'=>$v['id'],
                'name'=>$v['name'],
                'time'=>$v['create_time'],
                'num'=>$v['number'],
            ];
        }

        return $info;
    }

    //饮水服务详情页
    public function waterDetail(){
        $id=input('id');
        $result=WaterModel::where('id','eq',$id)->find();
        $this->assign('res',$result);
 //       echo json_encode($result);exit;
        return $this->fetch();
    }

    //电话宽带
    public function broadbandPhone()
    {
        $data=input('');
//        echo json_encode($data);exit;
            $broadbandModel = new BroadbandModel;
            $data['user_id']=session('userId');

            $result = $broadbandModel->allowField(true)->validate(true)->save($data);
            if ($result) {
                return $this->success("预约成功");
            } else {
                return $this->error($broadbandModel->getError());
            }

    }

//费用缴纳
    public function feedetail(){
        $type = input('t');
        $appid = input('id');
        $userid =session("userId");
        $userinfo=WechatUser::where(['userid'=>$userid])->find();
        $departmentId =$userinfo['department'];
        $map = ['company_id'=>$departmentId,'type'=>$type];
        $info = FeePayment::where($map)->find();
        $info['appid']=$appid;
        $info['payment_voucher']=isset($info['payment_voucher'])?unserialize($info["payment_voucher"]):"";
        $this->assign('info',json_encode($info));


        return $this->fetch();
    }
    /*记录*/
    public function history(){
        $info =[];
        $appid = input('id');
        $type = input('type');
        if($appid == 1){
            $userid =session("userId");
            $userinfo=WechatUser::where(['userid'=>$userid])->find();
            $departmentId =$userinfo['department'];
            $map = ['company_id'=>$departmentId,'type'=>$type];
            $list = FeePayment::where($map)->order('id desc')->select();
            foreach($list as $k=>$v){
                $info[$k]=[
                    'id'=>$v['id'],
                    'name' =>$v['name'],
                    'status'=>$v['status'],
                    'time'=>$v['expiration_time'],
                    'pay'=>$v['fee'],
                ];
            }

        }elseif($appid ==2){

            $info = $this->repairRecord();
        }elseif ($appid ==3){

            $info = $this->waterList();
        }elseif($appid==4){

            $info = $this->clearRecord();
        }elseif($appid==6){

            $info = $this->carRecord();
        }elseif($appid==7){

            $info = $this->pillarRecord();
        }

        $this->assign('info',json_encode($info));
        $this->assign('appId',$appid);

        return $this->fetch();
    }
    /*付款*/
    public function feeinfo(){
        $parkid= session('park_id');
        if (IS_POST){
            $feePayment = new FeePayment();
            $id = input('id');
            $appid =input('app_id');
            $data = input('post.');
            $datas["payment_voucher"] = serialize($data["payment_voucher"]);
            $datas['status']=1;
            $res=$feePayment->where('id',$id)->update($datas);
            if ($res){
                $msg="您的缴费信息正在核对中;核对完成后，将在个人中心中予以反馈;请耐心等待，确认成功后;发票将由园区工作人员在15个工作日之内送达企业";
                return  $this->success('成功',"",$msg);
            }else{

                return $this->error("上传失败");
            }

        }
        $parkInfo = Park::where('id',$parkid)->find();
        $this->assign('parkInfo',json_encode($parkInfo));

        return $this->fetch();

    }


    /* 记录详情*/
    public function  historyDetail(){
        $id = input('id');
        $appid = input('appid');
        if ($appid ==1){

            $infos = FeePayment::get($id);
            $info = [
                'name'=>$infos['name'],
                'expiration_time'=>$infos['expiration_time'],
                'img'=>isset($infos['payment_voucher'])?unserialize($infos['payment_voucher']):"",
            ];
        }elseif($appid ==3){

            $info=WaterService::get($id);
        }elseif ($appid ==6){
            $payment_voucher=CarparkRecord::where('id',$id)->field('payment_voucher,money,aging,carpark_id')->find();
            $info=CarparkService::where('id',$payment_voucher['carpark_id'])->find();
            //图片
            $info['img']=json_decode($payment_voucher['payment_voucher'],true);
            //费用总计
            $info['all_money']=$payment_voucher['money'];

            $park_id=session('park_id');
            $park=Park::where('id',$park_id)->field('carpark_deposit,carpark_price')->find();
            //押金
            $info['carpark_deposit']=$park['carpark_deposit'];
            //时长
            $info['aging']=$payment_voucher['aging'];
            //停车费
            $info['money']=$park['carpark_price']*$payment_voucher['aging'];

        }elseif ($appid==7){
            $record=ElectricityRecord::get($id);
            $service=ElectricityService::where('id',$record['service_id'])->find();
            $info['electricity_id']=$service['electricity_id'];//充电柱编号
            $info['name']=$service['name'];
            $info['mobile']=$service['mobile'];

            $park_id=session('park_id');
            $park=Park::where('id',$park_id)->field('charging_deposit,charging_price')->find();

            $info['aging']=$record['aging'];
            $info['carpark_deposit']=$park['charging_deposit'];//押金
            $info['money']=$park['charging_price']*$record['aging'];//充电费用
            $info['all_money']=$record['money'];//总费用
            $info['img']=json_decode($record['payment_voucher'],true);//图片
            $info['type']=$record['type'];

        }

        $this->assign('type',json_encode($appid));
        $this->assign('info',json_encode($info));
        return $this->fetch();

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

    public  function  test(){

      phpinfo();


    }


}


