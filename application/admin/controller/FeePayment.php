<?php
/**
 * Created by PhpStorm.
 * User: 贡7
 * Date: 2017/8/23
 * Time: 上午10:15
 */
namespace app\admin\controller;

use app\common\model\ParkCompany;
use app\common\model\FeePayment as FeePaymentModel;


class FeePayment extends Admin
{
    /*费用缴纳首页*/
    public function index(){
        $parkid =session("user_auth")['park_id'];
        $companyList=ParkCompany::where(['park_id'=>$parkid])->order('id  asc')->paginate();

        /// dump($companyList);
        $this->assign('list',$companyList);

        return $this->fetch();
    }
    /*费用的添加*/
    public function add(){
        $feepayment = new FeePaymentModel();
        if (IS_POST){
            $id =input('company_id');
            $type =input('type');
            $map = ['company_id'=>$id,'type'=>$type];
            $time = date("m",strtotime(input('expiration_time')));

           // return $time;
            $res = $feepayment->where($map)->select();
            foreach ($res as $k=>$v){
                $timeArray[$k] = date("m",strtotime($v['expiration_time']));
            }
            //return  dump($timeArray);
            ;
            if (in_array($time,$timeArray)){

                $re=$feepayment->validate(true)->where($map)->update(input('post.'));
                if ($re){

                    return $this->success('修改成功');
                }else{

                    return $this->error($feepayment->getError());
                }

            }else{
                $data =input('post.');
                $data['status'] = 0;
                $re=$feepayment->validate(true)->save($data);
                if ($re){

                    return $this->success('添加成功');
                }else{

                    return $this->error($feepayment->getError());
                }
            }

        }else{
            $status = [-1=>"删除",0=>"进行中",1=>"审核中",2=>"已缴费",3=>"审核失败"];
            $id = input('id');
            $company = ParkCompany::get($id);
            $list1 =$feepayment->where(['company_id'=>$id,'type'=>1,])->whereTime('expiration_time',"month")->find();
            //dump($list1);
            $list2 =$feepayment->where(['company_id'=>$id,'type'=>2])->whereTime('expiration_time',"month")->find();
            $list3 =$feepayment->where(['company_id'=>$id,'type'=>3])->whereTime('expiration_time',"month")->find();
            $list4 =$feepayment->where(['company_id'=>$id,'type'=>4])->whereTime('expiration_time',"month")->find();
            $list1['state'] =$status[$list1['status']];
            $list2['state'] =$status[$list2['status']];
            $list3['state'] =$status[$list3['status']];
            $list4['state'] =$status[$list4['status']];


            $this->assign('list1',$list1);
            $this->assign('list2',$list2);
            $this->assign('list3',$list3);
            $this->assign('list4',$list4);
            $this->assign('company',$company);

            return $this->fetch();
        }




    }

    public function changeState(){
        $id =input('id');
        $feepayment = new FeePaymentModel();
        $res =$feepayment->where('id',$id)->update(['status'=>2]);
        if ($res){

           return  $this->success("审核通过， 稍后自动刷新页面~");
        }else{

           return $this->error("审核失败");
        }


    }










}