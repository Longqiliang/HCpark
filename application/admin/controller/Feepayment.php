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
use app\index\model\WechatDepartment;
use app\index\model\WechatUser;
use app\common\behavior\Service;


class Feepayment extends Admin
{
    /*费用缴纳首页*/
    public function index()
    {
        $parkid = session("user_auth")['park_id'];
        $map = ['park_id' => $parkid];
        $search = input('search');
        if (!empty($search)) {
            $map['name'] = ['like', "%$search%"];
        }

        $companyList = ParkCompany::where($map)->order('id  asc')->paginate();

        /// dump($companyList);
        $this->assign('list', $companyList);

        return $this->fetch();
    }

    /*费用的添加*/
    public function add()
    {
        $feepayment = new FeePaymentModel();
        $datetime = date("Y-m", time());
        $months = date("Y-m-d", strtotime($datetime));
        $end_time = mktime(23, 59, 59, date('m', strtotime($months)) + 1, 00, date("y", time()));
        $start_time = $months;
        $end_time = date("Y-m-d", $end_time);
        if (IS_POST) {
            $timeArray = [];
            $id = input('company_id');
            $uid = input('uid');
            $type = input('type');
            $map = ['company_id' => $id, 'type' => $type];
            $time = date("m", strtotime(input('expiration_time')));

            // return $time;
            $res = $feepayment->where($map)->select();
            foreach ($res as $k => $v) {
                $timeArray[$k] = date("m", strtotime($v['expiration_time']));
            };
            if (in_array($time, $timeArray)) {
                $data = input('post.');
                unset($data['uid']);
                /*修改输入当月的数据*/
                $enter_time = date("Y-m", strtotime(input('expiration_time')));
                $start_time = date("Y-m-d", strtotime($enter_time));
                $end_time = mktime(23, 59, 59, date('m', strtotime($start_time)) + 1, 00, date("y", strtotime($enter_time)));
                $end_time = date("Y-m-d", $end_time);
                $re = $feepayment->validate(true)->where(['company_id' => $id, 'type' => $type, 'expiration_time' => ['between', [$start_time, $end_time]]])->update($data);

                if ($re) {

                    return $this->success('修改成功');
                } else {

                    return $this->error("sorry !! " . $feepayment->getError());
                }

            } else {
                $title = "" ;
                $type = [1=>"水电费",2=>"物业费",3=>"房租费",4=>"公耗费"];
                $data = input('post.');
                unset($data['uid']);
                $data['status'] = 0;
                $data['create_time'] = time();
                $re = $feepayment->validate(true)->save($data);
                $ids = $feepayment->getLastInsID();
                $title = $type[$data['type']];
                if ($re) {
                    $userList = WechatUser::where(['department'=>$data['company_id'],'fee_status'=>1])->select();

                    $message = [
                        "title" => $title."缴纳提示",
                        "description" => date('m月d日', time()) . "\n您的". $title."（到期时间：".$data['expiration_time']."）应缴纳".$data['fee']."元",
                        "url" => 'http://' . $_SERVER['HTTP_HOST'] . '/index/service/historyDetail/appid/1/id/'.$ids,
                    ];
                    foreach ($userList as $k=>$v){
                        Service::sendPersonalMessage($message,$v);
                    }

                    return $this->success('添加成功');
                } else {

                    return $this->error($feepayment->getError());
                }
            }

        } else {
            $status = [-1 => "删除", 0 => "未缴费", 1 => "审核中", 2 => "审核成功",3=>"审核失败"];
            $id = input('id');
            $company = ParkCompany::get($id);
            $list1 = $feepayment->where(['company_id' => $id, 'type' => 1, 'expiration_time' => ['between', [$start_time, $end_time]]])->find();
            $list2 = $feepayment->where(['company_id' => $id, 'type' => 2, 'expiration_time' => ['between', [$start_time, $end_time]]])->find();
            $list3 = $feepayment->where(['company_id' => $id, 'type' => 3, 'expiration_time' => ['between', [$start_time, $end_time]]])->find();
            $list4 = $feepayment->where(['company_id' => $id, 'type' => 4, 'expiration_time' => ['between', [$start_time, $end_time]]])->find();
            if ($list1) {
                $list1['state'] = $status[$list1['status']];
            }
            if ($list2) {
                $list2['state'] = $status[$list2['status']];
            }
            if ($list3) {
                $list3['state'] = $status[$list3['status']];
            }
            if ($list4) {
                $list4['state'] = $status[$list4['status']];
            }


            $this->assign('list1', $list1);
            $this->assign('list2', $list2);
            $this->assign('list3', $list3);
            $this->assign('list4', $list4);
            $this->assign('company', $company);

            return $this->fetch();
        }


    }

    /*审核*/
    public function changeState()
    {
        $type = [1=>"水电费",2=>"物业费",3=>"房租费",4=>"公耗费"];
        $id = input('id');
        $uid = input('uid');
        $userId = "";
        $feepayment = new FeePaymentModel();
        $res = $feepayment->where('id', $id)->update(['status' => $uid]);
        $feeInfo = $feepayment->where('id',$id)->find();
        $feeInfo['types'] = $type[$feeInfo['type']];
        if ($res) {
            $userList = WechatUser::where(['department'=> $feeInfo['company_id'],'fee_status'=>1])->select();
            foreach ($userList as $k=>$v){
                $userId[$k] = $v['userid'];
            }
            if ($uid ==2){
                $message = [
                    "title" => "费用缴纳提示",
                    "description" => date('m月d日', time()) . "\n您的".$feeInfo['types']."（到期时间：".$feeInfo['expiration_time']."）缴纳".$feeInfo['fee']."元确认成功。",
                    "url" => 'http://' . $_SERVER['HTTP_HOST'] . '/index/service/historyDetail/appid/1/id/'.$id,
                ];

            }else{


            }
            foreach ($userId as $k=>$v){
                Service::sendPersonalMessage( $message,$v);
            }

            return $this->success("审核成功， 稍后自动刷新页面~");
        } else {

            return $this->error("审核失败");
        }

    }

    /*缴费记录*/

    public function feeRecode()
    {
        $feepayment = new FeePaymentModel();
        $id = input('id');
        $company = ParkCompany::get($id);
        $list = $feepayment->where(['company_id' => $id])->order('id desc')->paginate();
        int_to_string($list, ['type' => [1 => "水电费", 2 => "物业费", 3 => "房租费", 4 => "公耗费"],
            'status' => [-1 => "删除", 0 => "审核中", 1 => "审核成功", 2 => "审核失败"]]);

        $this->assign('company', $company);
        $this->assign('list', $list);
        return $this->fetch();
    }

    /*权限管理*/
    public function manageuser()
    {
        $id = input('id');
        $list = WechatUser::where('department', $id)->order('id asc')->paginate();

        $this->assign('list', $list);
        return $this->fetch();
    }

    public function lookState()
    {
        $id = input('id');
        $uid = input('uid');
        $res = WechatUser::where('id', $id)->update(['fee_status' => $uid]);
        if ($res) {

            $this->success("设置成功");
        } else {

            $this->error("设置失败");
        }


    }

    /*显示凭证*/
    public function showImage(){
        $id = input("id");
        $html="";
        $feepayment = FeePaymentModel::get($id);
        if ($feepayment['payment_voucher']){
            $image = unserialize($feepayment['payment_voucher']);

            foreach($image as $value){
                $html .= "<div class='col-md-4'><img class='front_cover_img' src='$value' style='width: 150px;height: 200px;'></div>";

            }
        }

        return $html;

    }
}