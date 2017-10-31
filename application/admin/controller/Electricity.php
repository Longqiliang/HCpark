<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/8/25
 * Time: 下午1:24
 */

namespace app\admin\controller;

use  app\common\model\ElectricityService;
use  app\common\model\ElectricityRecord;
use  app\common\behavior\Service as ServiceModel;

class Electricity extends Admin
{
    public function index()
    {

        $ElectricityService = new ElectricityService();
        $parkId = session("user_auth")['park_id'];
        $search = input('search');
        if (!empty($search)) {
            $map = [
                'electricity_id' => array('like', '%' . $search . '%'),
                'status'=>array('neq',-1),
                'park_id'=>$parkId,
            ];
            $list = $ElectricityService->where($map)->order('status asc')->paginate();
            int_to_string($list, array(
                'status' => array(0 => '审核中', 1 => '审核通过', 2 => '审核失败'),
                'type' => array(1 => '新柱办理', 2 => '旧柱办理')
            ));

            $re = $ElectricityService->where(['status'=>0,'park_id'=>$parkId])->select();
            $data['data'] = $list;
            $data['num'] = count($re);


        } else {
            $map['status'] = array('neq', -1);
            $map['park_id']  = $parkId;
            $list = $ElectricityService->where($map)->order('status asc')->paginate();
            int_to_string($list, array(
                'status' => array(0 => '审核中', 1 => '审核通过', 2 => '审核失败'),
                'type' => array(1 => '新柱办理', 2 => '旧柱办理')
            ));

            $re = $ElectricityService->where(['status'=>0,'park_id'=>$parkId])->select();
            $data['data'] = $list;
            $data['num'] = count($re);
        }
        $this->assign('search',$search);
        $this->assign('list', $data);
        return $this->fetch();

    }

    public function check()
    {
        $ElectricityService = new ElectricityService();

        if (IS_POST) {

            $type = input('type');
            $id = input('id');
            $electricity_id = input('electricity_id');
            $checkRemark = input("check_remark");
            $message = [
                "title" => "车卡服务提示",
                "url" => 'http://' . $_SERVER['HTTP_HOST'] . '/index/service/historyDetail/appid/7/can_check/no/id/' . $id
            ];
            //通过
            if ($type == 1) {
                if (empty($electricity_id)) {
                    return $this->error("请填写 充电柱编号");
                }
                $record = $ElectricityService->where('id', $id)->find();
                //新柱申请
                if($record['type']==1){
                    $map['electricity_id']=$electricity_id;
                    $map['status']=1;
                    $is_has =$ElectricityService->where($map)->find();
                    if($is_has){
                        return $this->error('此柱已有使用者');
                    }
                }
                $record['status'] = 1;
                $record['electricity_id']=$electricity_id;
                $record->save();
                $userId =  $record['user_id'];
                //新柱
                if ($record['type'] == 1) {
                    $message ['description'] = "您的新柱缴费已经完成，请2小时后前往领取";

                } // 旧柱
                else {
                    $message ['description'] = "您的旧柱续费已经完成";
                }
                ServiceModel::sendPersonalMessage($message, $userId);

                return $this->success("审核成功");

            } //没通过
            elseif ($type == 2) {
                $record = $ElectricityService->where('id', $id)->find();
                $userId =  $record['user_id'];
                $record['status'] = 2;
                $record['check_remark'] = $checkRemark;
                $record->save();
                //新柱
                if ($record['type'] == 1) {
                    $message ['description'] = "您的新柱缴费无法通过审核\n备注:".$record['check_remark'];

                } // 旧柱
                else {
                    $message ['description'] = "您的旧柱续费无法通过审核\n备注:".$record['check_remark'];
                }
                ServiceModel::sendPersonalMessage($message,$userId);

                return $this->success("审核成功");
            }
        } else {
            $id = input('id');
            $info = $ElectricityService->where('id', $id)->find();
            $info['payment_voucher'] = json_decode($info['payment_voucher']);
            $info['type'] = $info['type'] == 1 ? "新柱办理" : "旧柱续费";
            $info['user'] = isset($info->user->name )?$info->user->name:"";
            switch ($info['status']) {
                case 0:
                    $info['status_text'] = "审核中";
                    break;
                case 1:
                    $info['status_text'] = "审核通过";
                    break;
                case 2:
                    $info['status_text'] = "审核失败";
                    break;
            }
            $this->assign('info', $info);
            return $this->fetch();
        }
    }
    public function del()
    {
        $CardparkRecord = new ElectricityService();
        $result = $CardparkRecord->where('id', input('id'))->find();
        $result['status'] = -1;
        $result->save();

        if ($result) {
            $this->redirect('index');
        } else {
            $this->error('删除失败');
        }
    }

    //逻辑删除
    public function moveToTrash() {
        $ids = input('ids/a');
        $result = ElectricityService::where('id', 'in', $ids)->update(['status' => -1]);
        if($result) {
            return $this->success('删除成功');
        } elseif(!$result) {
            return $this->error('删除失败');
        }
    }


    public function pushImg()
    {
        $id = input('id');
        $path = input('images');
        $path=str_replace('\\','/',$path);
        $car = ElectricityService::get($id);
        $car['charge_voucher'] = $path;
        $car->save();
        return $this->success('上传成功');
    }

}
