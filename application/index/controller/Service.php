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
use  app\index\model\PropertyServer;
use app\index\model\WaterService as WaterModel;
use app\index\model\BroadbandPhone as BroadbandModel;

use  app\index\model\AdvertisingRecord;
use  app\index\model\AdvertisingService;
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
           //公共场所
            case 8:
                $re = $AdService->where('park_id',$park_id)->select();
                $info['adlist']=json_encode($re);
                break;
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


    public  function  newCard(){
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


    public  function  nextNewCard(){

        $data=input('');
        $this->assign('data',$data);
        return $this->fetch();

    }





    public  function  addNewCard(){

        $CarparkRecord = new CarparkRecord();
        $CardparkService= new CarparkService();

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



    }




    public  function  oldCard(){
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

    public  function  nextOldCard(){

        $data=input('');
        $this->assign('data',$data);
        return $this->fetch();

    }


    public  function  keepOldCard(){

        $CarparkRecord = new CarparkRecord();
        $CardparkService= new CarparkService();
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





    }
    //车卡记录
    public  function  carRecord(){
        $service =new CarparkService();
        $user_id= session('userId');

        $list=$service->where('user_id',$user_id)->select();
        $this->assign('list',json_encode($list));
        return $this->fetch();



    }

        //大厅广告位预约
        public function advertise(){
            $adService=new AdvertisingService();
            $adRecord = new AdvertisingRecord();
            //今天结束时间
            $endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
            //本月结束时间
            $endThismonth=mktime(23,59,59,date('m'),date('t'),date('Y'));
            $map['order_time']=array('between',array($endToday,$endThismonth));
            $map['service_id']=1;
            $map['status']=array('eq',1);
            $list =$adRecord->where($map)->select();
            $service =$adService->where('id',1)->find();
            $this->assign('price',$service['price']);
            $this->assign('record',json_encode($list));
            return $this->fetch();
        }



      //大厅广告位（下一步）
      public  function  nextAdvertise(){
            $park_id=session('$park_id');
            $Park=new Park();
            $data=input('');
            $info = $Park->where("id",$park_id)->find();
            $data['ailpay_user']=$info['ailpay_user'];
            $data['payment_alipay']=$info['payment_alipay'];
            $this->assign('data',$data);
            return $this->fetch();
      }

    //大厅广告位（提交）
    public  function  submitAdvertise(){
        $ad =new AdvertisingRecord();
       $user_id =session('userId');
        $data=input('');
        $num = count($data['order_times']);
        $record=array();
        $creat_time=time();
        foreach ($data['order_times'] as $value){
            $info=[
              'create_user'=>$user_id,
                'service_id'=>1,
                'payment_voucher'=>$data['payment_voucher'],
                'order_time'=>$value,
                 'create_time'=>$creat_time,
                  'statute'=>1
            ];
            array_push($record,$info);
        }
         $re = $ad ->save($record);
        if($re){
            return $this->success('成功');
        }
         else{
             return $this->error('成功');
         }
    }




        //大厅广告位月份切换
       public  function   changeMonth(){
           $adRecord = new AdvertisingRecord();
        //数字（几月）
        $month=input('month');


           $beginThismonth=mktime(0,0,0,$month,1,date('Y'));

           $endThismonth=mktime(23,59,59,$month,date('t'),date('Y'));
           $map['order_time']=array('between',array($beginThismonth,$endThismonth));
           $map['service_id']=1;
           $map['status']=array('eq',1);
           $list =$adRecord->where($map)->select();

           return json_encode($list);
       }








        //公共区服务
        public function publicservice(){



            return $this->fetch();
        }


        public function hall(){
            return $this->fetch();
        }

    /*物业报修*/
    public function repair(){
        $userid =session("userId");
        $parkid=session('park_id');
        if (IS_POST){
            $property =new PropertyServer();
            $data = input('post.');
            $data['user_id']=$userid;
            $data['park_id']=$parkid;

            $res=$property->allowField(true)->save($data);

            if ($res){

                return $this->success("报修成功");
            }else{

                return $this->error("报修失败");
            }

        }
        $parkInfo=Park::where('id',$parkid)->find();
        $userinfo=WechatUser::where(['userid'=>$userid])->find();
        $data =[
            'name'=>$userinfo['name'],
            'mobile'=>$userinfo['mobile'],
            'propretyMobile'=>$parkInfo['property_phone']
        ];

        //dump($data);
        $this->assign('data',json_encode($data));

        return  $this->fetch();
    }
    /*保洁服务*/
    public function clear(){
        $userid =session("userId");
        $parkid=session('park_id');
        if ($_POST){
            $data = input('post.');
            $data['user_id']=$userid;
            $data['park_id']=$parkid;
            $property =new PropertyServer();

            $res=$property->allowField(true)->save($data);
            if ($res){

                return $this->success("报修成功");
            }else{

                return $this->error("报修失败");
            }

        }

        $parkInfo=Park::where('id',$parkid)->find();
        $userinfo=WechatUser::where(['userid'=>$userid])->find();
        $data =[
            'name'=>$userinfo['name'],
            'mobile'=>$userinfo['mobile'],
            'propretyMobile'=>$parkInfo['property_phone']
        ];

        //dump($data);
        $this->assign('data',json_encode($data));

        return  $this->fetch();
    }
    /*物业报修记录*/
    public function repairRecord(){

        $list = PropertyServer::where(['type'=>['<',4],'status'=>['in',[0,1]]])->order('create_time desc')->paginate();
        int_to_string($list,['type'=>[1=>'空调报修',2=>"电梯报修",3=>"其他报修"],
                             'status'=>[0=>"进行中",1=>"已完成"]
                    ]);

        $this->assign('list',$list);

        return $this->fetch();
    }

    /*保洁服务记录*/

    public function clearRecord(){
        $list = PropertyServer::where(['type'=>['<',4],'status'=>['in',[0,1]]])->order('create_time desc')->paginate();
        int_to_string($list,['type'=>[4=>'保洁记录'], 'status'=>[0=>"进行中",1=>"已完成"]]);

        $this->assign('list',$list);

        return $this->fetch();

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
        if ($_POST) {
            $waterModel = new WaterModel;
            $_POST['userid']=session('userId');
            $result = $waterModel->allowField(true)->validate(true)->save($_POST);
            if ($result) {
                //预约成功
                return $this->success("预约成功");
            } else {
                return $this->error($waterModel->getError());
            }

        } else {
            $userid = session('userId');
            $contact = WechatUser::where('userid', 'eq', $userid)->field('name,mobile')->find();
            $this->assign('contact', $contact);
            return $this->fetch();
        }
    }

    //饮水服务列表页
    public function waterList(){
        //分页total
        $total=input('total');
        $userid = session('userId');
        $map = [
            'status'=> "1",
            'userid'=>$userid,
        ];
        $list = WaterModel::where($map)->order('id desc')->paginate($total);
        $this->assign('list',$list);
        return $this->fetch();
    }

    //饮水服务详情页
    public function waterDetail(){
        $id=input('id');
        $result=WaterModel::where('id','eq',$id)->find();
        $this->assign('res',$result);
        echo json_encode($result);exit;
        return $this->fetch();
    }

    //电话宽带
    public function broadbandPhone()
    {
        if ($_POST) {
            $broadbandModel = new BroadbandModel;
            $_POST['user_id']=session('userId');

            $result = $broadbandModel->allowField(true)->validate(true)->save($_POST);
            if ($result) {
                return $this->success("预约成功");
            } else {
                return $this->error($broadbandModel->getError());
            }
        } else {
            return $this->fetch();
        }
    }



}