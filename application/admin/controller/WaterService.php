<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/17
 * Time: 9:33
 */

namespace app\admin\controller;

use app\common\model\WaterService as WaterModel;
use app\common\model\WechatUser;
use app\common\model\WaterType;
use think\Db;
use app\common\behavior\Service as ServiceModel;
use PHPExcel;

class WaterService extends Admin
{
    public function index()
    {
        $parkid = session("user_auth")['park_id'];


        $search = input('search');
        $map['s.status'] = ['neq', -1];
        if ($search != '') {
            $map['s.name'] = ['like', '%' . $search . '%'];
        }

        $list = Db::table('tb_water_service')
            ->alias('s')
            ->join('__WATER_TYPE__ t', 't.id=s.water_id')
            ->field('s.id,s.userid,s.name,s.mobile,s.address,s.number,s.create_time,s.status,s.check_remark,s.price totalprice,t.water_name,t.format ,t.price ')
            ->where('s.park_id', 'eq', $parkid)
            ->where($map)
            ->order('create_time desc')
            ->paginate();


        $list2 = Db::table('tb_water_service')
            ->alias('s')
            ->join('__WATER_TYPE__ t', 't.id=s.water_id')
            ->field('s.id,s.userid,s.name,s.mobile,s.address,s.number,s.create_time,s.status,s.check_remark,s.price totalprice,t.water_name,t.format ,t.price ')
            ->where('s.park_id', 'eq', $parkid)
            ->where('s.status <2 and s.status>-1')
            ->order('create_time desc')
            ->paginate();
        //ECHO json_encode($list);
        $this->assign('count', count($list2));
        $this->assign('list', $list);
        $this->assign('park_id', $parkid);
        return $this->fetch();
    }

   //详情页
    public function detail()
    {
        $id = input('id');
        $result = WaterModel::where('id', 'eq', $id)->find();
        $this->assign('res', $result);
        return $this->fetch();
    }

    //饮水种类管理
    public function watertype()
    {
        $result = WaterType::where('status', 'neq', -1)->paginate();
        int_to_string($result, $map = array('status' => array(0 => '禁用', 1 => '启用')));
        $this->assign('list', $result);
        return $this->fetch();
    }

    //饮水种类编辑
    public function typeEdit()
    {
        $water = new WaterType();
        $data = input('');
        $id = isset($data['id']) ? $data['id'] : "";
        if (!empty($id)) {
            $reult = $water->allowField(true)->save($data, ['id' => $id]);
        } else {
            unset($data['id']);

            $reult = $water->allowField(true)->save($data);
        }
        if ($reult) {
            return $this->success("成功");

        } else {

            return $this->error($water->getError());
        }
    }


    public function deleteWaterType()
    {
        $ids = input('ids/a');;
        $re = WaterType::Where('id', 'in', $ids)->update(['status' => -1]);
        if ($re) {
            return $this->success('删除成功', url('WaterService/watertype'));
        } elseif (!$re) {
            return $this->error('删除失败');
        }

    }


//逻辑删除
    public function moveToTrash()
    {
        $ids = input('ids/a');
        $result = WaterModel::where('id', 'in', $ids)->update(['status' => -1]);
        if ($result) {
            return $this->success('删除成功', url('WaterService/index'));
        } elseif (!$result) {
            return $this->error('删除失败');
        }
    }

    //完成
    public function complete()
    {

        $id = input('id');
        $uid = input('uid');
        $remark = input('check_remark');
        $result = WaterModel::where('id', 'in', $id)->update(['status' => $uid, 'check_remark' => $remark]);
        $data = WaterModel::get($id);
        $userId = $data['userid'];
        if ($result) {
            if ($uid == 1) {
                $message = [
                    "title" => "饮水服务提示",
                    "description" => "送水地点：" . $data['address'] . "\n送水桶数：" . $data['number'] . "\n联系人员：" . $data['name'] . "\n联系电话：" . $data['mobile'],
                    "url" => 'http://' . $_SERVER['HTTP_HOST'] . '/index/service/historyDetail/appid/3/can_check/yes/id/' . $id,
                ];
            } else {
                $message = [
                    "title" => "饮水服务提示",
                    "description" => date('m月d日', time()) . "\n饮水服务暂时无法提供\n备注:" . $data['check_remark'],
                    "url" => 'http://' . $_SERVER['HTTP_HOST'] . '/index/service/historyDetail/appid/3/can_check/yes/id/' . $id,
                ];

            }

            ServiceModel::sendPersonalMessage($message, $userId);
            $this->success("已审核");
        } else {

            $this->error("未成功");
        }
    }

    public function outexcel()
    {
        $parkid = session("user_auth")['park_id'];
        $map['s.status'] = ['neq', -1];
        $list = Db::table('tb_water_service')
            ->alias('s')
            ->join('__WATER_TYPE__ t', 't.id=s.water_id')
            ->field('s.id,s.userid,s.name,s.mobile,s.address,s.number,s.create_time,s.status,s.check_remark,s.price totalprice,t.water_name,t.format ,t.price ,s.price totalprice' )
            ->where('s.park_id', 'eq', $parkid)
            ->where($map)
            ->order('create_time desc')
            ->select();
        int_to_string($list, $map = array('status' => array(0 => '提交预约', 1 => '确认接单',2=>"取消接单",3=>"确认送达")));
        $excel = new PHPExcel();
        $letter = array('A', 'B', 'C', 'D', 'E', 'F', 'F', 'G', 'H', 'I');
        $celltitle = [
            '联系人', '送水地址', '送水桶数', '送水种类', '送水规格', '送水单格','送水总价', '联系电话', '创建时间', '状态'
        ];
        foreach ($list as $key => $value) {
            $cellData[$key] = [$value['name'], $value['address'], $value['number'], $value['water_name'], $value['format'], $value['price'],$value['totalprice']  , $value['mobile'], date('Y-m-d H:i:s',$value['create_time']), $value['status_text']];
        }
        for ($i = 0; $i < count($celltitle); $i++) {
            $excel->getActiveSheet()->setCellValue("$letter[$i]1", "$celltitle[$i]");
        }

        $data = $cellData;
        //填充表格信息
        for ($i = 2; $i <= count($data) + 1; $i++) {
            $j = 0;
            foreach ($data[$i - 2] as $key => $value) {
                $excel->getActiveSheet()->setCellValue("$letter[$j]$i", "$value");
                $j++;
            }
        }
        //创建Excel输入对象
        $write = new \PHPExcel_Writer_Excel5($excel);
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Disposition:attachment;filename="饮水记录表.xls"');
        header("Content-Transfer-Encoding:binary");
        $write->save('php://output');

    }
  public  function  test(){
      $map['s.status'] = ['neq', -1];
      $list = Db::table('tb_water_service')
          ->alias('s')
          ->join('__WATER_TYPE__ t', 't.id=s.water_id')
          ->field('s.id,s.userid,s.name,s.mobile,s.address,s.number,s.create_time,s.status,s.check_remark,s.price totalprice,t.water_name,t.format ,t.price ,s.price totalprice' )
          ->where('s.park_id', 'eq', 80)
          ->where($map)
          ->order('create_time desc')
          ->select();
  echo  json_encode($list);


  }
    /* private function a()
     {
         Excel::create('学生成绩', function ($excel) use ($cellData) {
             $excel->sheet('score', function ($sheet) use ($cellData) {
                 $sheet->rows($cellData);
             });
         })->export('xls');
     }*/


}