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
        $ElectricityRecord = new ElectricityRecord();
        $ElectricityService = new ElectricityService();
        $search = input('search');
        if (!empty($search)) {
            $map = [
                'electricity_id' => array('like', '%' . $search . '%'),
            ];
            $list = $ElectricityService->where($map)->order('status asc')->paginate();
            $ids = array();
            foreach ($list as $value) {
                array_push($ids, $value['id']);
            }
            unset($map['electricity_id']);
            $map['service_id'] = array('in', $ids);
            $map['status'] = array('neq', -1);
            $list = $ElectricityRecord->where($map)->order('status asc')->paginate();
            $list2 = $ElectricityRecord->where($map)->order('status asc')->paginate();
            int_to_string($list2, array(
                'status' => array(0 => '审核中', 1 => '审核通过', 2 => '审核失败'),
                'type' => array(1 => '新柱办理', 2 => '旧柱办理')
            ));
            foreach ($list2 as $value) {
                $value['electricity_id'] = !empty($value->findService->electricity_id) ? $value->findService->electricity_id : "暂无";
            }
            $re = $ElectricityRecord->where('status', 0)->select();
            $data['data'] = $list2;
            $data['num'] = count($re);


        } else {
            $map['status'] = array('neq', -1);
            $list = $ElectricityRecord->where($map)->order('status asc')->paginate();
            int_to_string($list, array(
                'status' => array(0 => '审核中', 1 => '审核通过', 2 => '审核失败'),
                'type' => array(1 => '新柱办理', 2 => '旧柱办理')
            ));
            foreach ($list as $value) {
                $value['electricity_id'] = !empty($value->findService->electricity_id) ? $value->findService->electricity_id : "暂无";
            }
            $re = $ElectricityRecord->where('status', 0)->select();
            $data['data'] = $list;
            $data['num'] = count($re);
        }
        $this->assign('list', $data);
        return $this->fetch();

    }

    public function check()
    {
        $ElectricityRecord = new ElectricityRecord();

        if (IS_POST) {
            $ElectricityService = new ElectricityService();
            $type = input('type');
            $id = input('id');
            $electricity_id = input('electricity_id');
            //通过
            if ($type == 1) {
                if (empty($electricity_id)) {
                    return $this->error("请填写 充电柱编号");
                }
                $record = $ElectricityRecord->where('id', $id)->find();
                $record['status'] = 1;
                $record->save();
                $service = $ElectricityService->where('id', $record['service_id'])->find();
                $service['status'] = 1;
                $service['electricity_id'] = $electricity_id;
                $service->save();

                return $this->success("审核成功");

            } //没通过
            elseif ($type == 2) {
                $record = $ElectricityRecord->where('id', $id)->find();
                $record['status'] = 2;
                $record->save();
                return $this->success("审核成功");
            }


        }else{
            $id=input('id');
            $info =$ElectricityRecord->where('id',$id)->find();
            $info['payment_voucher']=json_decode($info['payment_voucher']);
            $info['name'] = isset($info->findService->name)?$info->findService->name:"";
            $info['mobile'] = isset($info->findService->mobile)?$info->findService->mobile:"";
            $info['user'] = isset($info->findService->user->name  )?$info->findService->user->name:"";
            $info['type']=$info['type']==1?"新柱办理":"旧柱续费";
            $info['electricity_id'] = isset($info->findService->electricity_id)?$info->findService->electricity_id:"";
            switch ($info['status']){
                case 0: $info['status_text']="审核中"; break;
                case 1: $info['status_text']="审核通过"; break;
                case 2: $info['status_text']="审核失败"; break;
            }

$this->assign('info', $info);
return $this->fetch();
}
}
public
function del()
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

public
function change()
{
    $ElectricityRecord = new ElectricityRecord();
    $ElectricityService = new ElectricityService();
    $id = input('id');
    $electricity_id = input('electricity_id');
    if (empty($park_card)) {
        return $this->error("请填写 充电柱编号");
    }
    $record = $ElectricityRecord->where('id', $id)->find();
    $map = [
        'electricity_id' => $electricity_id
    ];
    $service = $ElectricityService->where('id', $record['service_id'])->Update($map);
    if ($service != false) {
        return $this->success("修改成功");
    } else {
        return $this->error("修改失败");
    }


}


}
