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

class Electricity extends Admin
{
    public function index()
    {

        $ElectricityService = new ElectricityService();
        $search = input('search');
        if (!empty($search)) {
            $map = [
                'electricity_id' => array('like', '%' . $search . '%'),
                'status'=>array('neq',-1)
            ];
            $list = $ElectricityService->where($map)->order('status asc')->paginate();
            int_to_string($list, array(
                'status' => array(0 => '审核中', 1 => '审核通过', 2 => '审核失败'),
                'type' => array(1 => '新柱办理', 2 => '旧柱办理')
            ));

            $re = $ElectricityService->where('status', 0)->select();
            $data['data'] = $list;
            $data['num'] = count($re);


        } else {
            $map['status'] = array('neq', -1);
            $list = $ElectricityService->where($map)->order('status asc')->paginate();
            int_to_string($list, array(
                'status' => array(0 => '审核中', 1 => '审核通过', 2 => '审核失败'),
                'type' => array(1 => '新柱办理', 2 => '旧柱办理')
            ));

            $re = $ElectricityService->where('status', 0)->select();
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
                return $this->success("审核成功");

            } //没通过
            elseif ($type == 2) {
                $record = $ElectricityService->where('id', $id)->find();
                $record['status'] = 2;
                $record->save();
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

}
