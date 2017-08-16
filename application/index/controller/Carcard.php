<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/8/16
 * Time: 下午8:42
 */

namespace app\index\controller;
use  app\index\model\CarparkRecord;
use  app\index\model\CompanyService;

class Carcard extends  Base
{

    public  function  index(){
      $CarparkRecord = new CarparkRecord();
      $CompanyService= new CompanyService();
      $id = session('userId');
      $map=[
          'user_id'=>$id,
          'status'=>1
      ];

      $usercard =$CompanyService->where($map)->select();
      if(count($usercard)>0){

        $context="old";

      }else{

          $context="new";
      }
        $this->assign('type',$context);

      return $this->fetch();

    }



    public  function  NewCard(){
        $data=input('');
        $this->assign('data',$data);

        return $this->fetch();
    }



    public  function  addNewCard(){

        $CarparkRecord = new CarparkRecord();
        $CompanyService= new CompanyService();
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

            $re = $CompanyService->save($service);
            $record=[
                'type'=>1,
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

    public  function  OldCard(){

        $data=input('');
        $this->assign('data',$data);

        return $this->fetch();
    }



    public  function  keepOldCard(){

        $CarparkRecord = new CarparkRecord();
        $CompanyService= new CompanyService();
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

            $re = $CompanyService->save($service);
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

}