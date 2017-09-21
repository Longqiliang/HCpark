<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/8/16
 * Time: 下午9:45
 */

namespace app\admin\controller;

use  app\common\model\CarparkRecord;
use  app\common\model\CarparkService;
use  app\common\behavior\Service as ServiceModel;

class Carcard extends Admin
{
    public function index()
    {
        $CardparkService = new CarparkService();
        $search = input('search');
        if (!empty($search)) {
            $map = [
                'park_card' => array('like', '%' . $search . '%'),
                'status'=>array('neq',-1)
            ];
            $list = $CardparkService->where($map)->order('status asc')->paginate();
        } else {
            $map['status'] = array('neq', -1);
            $list = $CardparkService->where($map)->order('status asc')->paginate();
        }
        int_to_string($list, array(
            'status' => array(0 => '审核中', 1 => '审核通过', 2 => '审核失败'),
            'type' => array(1 => '新卡办理', 2 => '旧卡办理')
        ));
        $re = $CardparkService->where('status', 0)->select();
        $data['data'] = $list;
        $data['num'] = count($re);
        $this->assign('search',$search);
        $this->assign('list', $data);
        return $this->fetch();

    }

    public function check()
    {
        $CardparkService = new CarparkService();
        if (IS_POST) {
            $type = input('type');
            $id = input('id');
            $park_card = input('park_card');
            $checkRemark = input("check_remark");
            $message = [
                "title" => "车卡服务提示",
                "url" => 'http://' . $_SERVER['HTTP_HOST'] . '/index/service/historyDetail/appid/6/can_check/no/id/' . $id
            ];
            //通过
            if ($type == 1) {
                if (empty($park_card)) {
                    return $this->error("请填写 停车卡号");
                }
                $record = $CardparkService->where('id', $id)->find();
                $userId =  $record['user_id'];
                $record['park_card'] = $park_card;
                $record['status'] = 1;
                $record->save();
                if ($record['type'] == 1) {
                    $message ['description'] = "您的新卡缴费已经完成，请2小时后前往领取";

                } // 旧卡
                else {
                    $message ['description'] = "您的旧卡续费已经完成";
                }
                ServiceModel::sendPersonalMessage($message,$userId);
                return $this->success("审核成功");
            } //没通过
            elseif ($type == 2) {
                $record = $CardparkService->where('id', $id)->find();
                $userId =  $record['user_id'];
                $record['park_card'] = $park_card;
                $record['status'] = 2;
                $record['check_remark'] = $checkRemark;
                $record->save();
                //新卡
                if ($record['type'] == 1) {
                    $message ['description'] = "您的新卡缴费无法通过审核\n备注:".$record['check_remark'];

                } // 旧卡
                else {
                    $message ['description'] = "您的旧卡续费无法通过审核\n备注:".$record['check_remark'];
                }
                ServiceModel::sendPersonalMessage($message,$userId);
                return $this->success("审核成功");
            }
        } else {
            $id = input('id');
            $info = $CardparkService->where('id', $id)->find();
            $info['payment_voucher'] = json_decode($info['payment_voucher']);
            $info['type'] = $info['type'] == 1 ? "新卡办理" : "旧卡续费";
            $info['user']=isset($info->user->name)?$info->user->name:"";
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
        $CardparkService = new CarparkService();
        $result = $CardparkService->where('id', input('id'))->find();
        $result['status'] = -1;
        $result->save();
        if ($result) {
            $this->redirect('index');
        } else {
            $this->error('删除失败');
        }
    }



}