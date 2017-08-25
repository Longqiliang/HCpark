<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/8/25
 * Time: 下午2:14
 */

namespace app\admin\controller;
use  app\common\model\AdvertisingService;
use  app\common\model\AdvertisingRecord;

class PublicArea extends Admin
{
    public  function  index(){
        $AdvertisingService=new AdvertisingService();
        $park_id=session('user_auth')['park_id'];

        $search=input('search');
        if(!empty($search)){
            $map['park_id']=$park_id;
            $map['status']=1;
            $map=[
                'abstract'=>array('like','%'.$search.'%'),
            ];
            $data = $AdvertisingService->where($map)->order('id asc')->paginate();
            foreach ($data as $value){
                $value['park_id']=isset($value->findPark->name)?$value->findPark->name:"";

            }
            int_to_string($data, array(
                'status' => array(0=>'禁用',1=>'启用'),
                'type'=> array(1=> '大厅广告位', 2=>'多功能厅'  ,3=> 'LED灯' )
            ));

        }else{
           $map['park_id']=$park_id;
            $map['status']=1;
            $data= $AdvertisingService->where($map)->order('id asc')->paginate();
            foreach ($data as $value){
                $value['park_id']=isset($value->findPark->name)?$value->findPark->name:"";

            }
            int_to_string($data, array(
                'status' => array(0=>'禁用',1=>'启用'),
                'type' => array(1=> '大厅广告位', 2=>'多功能厅'  ,3=> 'LED灯' )
            ));



        }

        $this->assign('list',$data);
        return $this->fetch();
    }

    public  function  show(){
        $AdvertisingRecord=new AdvertisingRecord();
        $AdvertisingService=new AdvertisingService();
        $id=input('id');
        $type=input('type');
        switch ($type){
            case 1:
                $create_time=array();
                $data=array();
                $time=array();
                $map=[
                    'service_id'=>$id,
                    'status'=>array('neq',0)
                ];
                $serviceInfo =$AdvertisingService->where('id',1)->find();
                $list=$AdvertisingRecord->where($map)->select();
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
                        'create_time'=>$onetime,
                        'user'=>isset($info->findUser->name)?$info->findUser->name:"未找到",
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
                    $re['type']=1;
                    array_push($data,$re);
                }
                break;
            case 2:break;


            case 3:break;


        }

        $this->assign('data',$data);
        return $this->fetch();
    }
    public function cancel() {
        $create_time=input('create_time');
        $type=input('type');
       $adr = new AdvertisingRecord();switch ($type){
      case 1:
      $list =$adr->where('create_time',$create_time)->select();
      foreach ($list as $value){
      $value['status']=0;
      $value->save();
      }
      break;

      case 2:break;
      case 3:break;
  }

        if(count($list)>0) {
            $this->sucess('取消成功');
        } else {
            $this->error('取消失败');
        }
    }





}
