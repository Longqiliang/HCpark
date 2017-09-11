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

class Carcard extends Admin
{
    public function index()
    {
        $CardparkRecord = new CarparkRecord();
        $CardparkService = new CarparkService();
        $search = input('search');
        if (!empty($search)) {


            $map = [
                'park_card' => array('like', '%' . $search . '%'),

            ];
            $list = $CardparkService->where($map)->order('status asc')->paginate();
            $ids = array();
            foreach ($list as $value) {
                array_push($ids, $value['id']);
            }
            unset($map['park_card']);
            $map['carpark_id'] = array('in', $ids);
            $map['status'] = array('neq', -1);
            $list = $CardparkRecord->where($map)->order('status asc')->paginate();
            $list2 = $CardparkRecord->where($map)->order('status asc')->paginate();
            int_to_string($list2, array(
                'status' => array(0 => '审核中', 1 => '审核通过', 2 => '审核失败'),
                'type' => array(1 => '新卡办理', 2 => '旧卡办理')
            ));
            foreach ($list2 as $value) {
                $value['park_card'] = !empty($value->findService->park_card) ? $value->findService->park_card : "暂无";
            }
            $re = $CardparkRecord->where('status', 0)->select();
            $data['data'] = $list2;
            $data['num'] = count($re);
        } else {
            $map['status'] = array('neq', -1);
            $list = $CardparkRecord->where($map)->order('status asc')->paginate();
            int_to_string($list, array(
                'status' => array(0 => '审核中', 1 => '审核通过', 2 => '审核失败'),
                'type' => array(1 => '新卡办理', 2 => '旧卡办理')
            ));
            foreach ($list as $value) {
                $value['park_card'] = !empty($value->findService->park_card) ? $value->findService->park_card : "暂无";
            }
            $re = $CardparkRecord->where('status', 0)->select();
            $data['data'] = $list;
            $data['num'] = count($re);
        }

        $this->assign('list', $data);
        return $this->fetch();

    }

    public function check()
    {
        $CardparkRecord = new CarparkRecord();
        if (IS_POST) {
            $CardparkService = new CarparkService();
            $type = input('type');
            $id = input('id');
            $park_card = input('park_card');
            //通过
            if ($type == 1) {
                if (empty($park_card)) {
                    return $this->error("请填写 停车卡号");
                }
                $record = $CardparkRecord->where('id', $id)->find();
                $record['status'] = 1;
                $record->save();
                $service = $CardparkService->where('id', $record['carpark_id'])->find();
                $service['park_card'] = $park_card;
                $service->save();

                return $this->success("审核成功");

            } //没通过
            elseif ($type == 2) {
                $record = $CardparkRecord->where('id', $id)->find();
                $record['status'] = 2;
                $record->save();
                return $this->success("审核成功");
            }
        } else {
            $id = input('id');
            $info = $CardparkRecord->where('id', $id)->find();
            $info['payment_voucher'] = json_decode($info['payment_voucher']);
            $info['name'] = isset($info->findService->name) ? $info->findService->name : "";
            $info['mobile'] = isset($info->findService->mobile) ? $info->findService->mobile : "";
            $info['car_card'] = isset($info->findService->car_card) ? $info->findService->car_card : "";
            $info['people_card'] = isset($info->findService->people_card) ? $info->findService->people_card : "";
            $info['user'] = isset($info->findService->user->name) ? $info->findService->user->name : "";
            $info['type'] = $info['type'] == 1 ? "新卡办理" : "旧卡续费";
            $info['park_card'] = isset($info->findService->park_card) ? $info->findService->park_card : "";
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
        $CardparkRecord = new CarparkRecord();
        $result = $CardparkRecord->where('id', input('id'))->find();
        $result['status'] = -1;
        $result->save();
        if ($result) {
            $this->redirect('index');
        } else {
            $this->error('删除失败');
        }
    }

    public function change()
    {
        $CardparkService = new CarparkService();
        $CardparkRecord = new CarparkRecord();
        $id = input('id');
        $park_card = input('park_card');
        if (empty($park_card)) {
            return $this->error("请填写 停车卡号");
        }
        $record = $CardparkRecord->where('id', $id)->find();
        $map = [
            'park_card' => $park_card
        ];
        $service = $CardparkService->where('id', $record['carpark_id'])->Update($map);
        if ($service != false) {
            return $this->success("修改成功");
        } else {
            return $this->error("修改失败");
        }


    }


}