<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/12/13
 * Time: 上午10:46
 */

namespace app\admin\controller;

use app\common\model\ActivityComment;
use app\common\model\Activity as ActivityModel;
use PHPExcel;
class Activity extends Admin
{
    /**
     * 主页列表
     */
    public function index()
    {
        $park_id = session('user_auth')['park_id'];
        $search= input('search');

        $map = array(
            'status' => array('gt',-1),
            'park_id'=>$park_id
        );
        if(!empty($search)){
            $map['name']=['like','%'.$search.'%'];
        }
        $activity = new ActivityModel();
        $list = $activity->where($map)->order('start_time asc')->paginate(12,false,['query' => request()->param()]);
        int_to_string($list, array(
            'status' => array(0 => "禁用",1=>'预报名',2=>'开始报名'),
        ));

        $this->assign('list', $list);
        $this->assign('search', $search);
        return $this->fetch();
    }

    /*
     * 活动添加
     */
    public function add()
    {
        if (IS_POST) {
            $data = input('');
            $activity = new ActivityModel();
            $park_id = session('user_auth')['park_id'];
            $data['start_time'] = strtotime($data['start_time']);
            $data['end_time'] = strtotime($data['end_time']);
            $data['park_id'] = $park_id;
            if (empty($data['id'])) {
                unset($data['id']);
                $info = $activity->validate(false)->
                save($data);
            }else{
                $info = $activity->validate(false)->save($data,['id'=>$data['id']]);
            }
            if ($info) {
                return $this->success("保存成功", Url('Activity/index'));
            } else {
                return $this->error($activity->getError());
            }
        } else {
            $a = array('1'=>'a','2'=>'b','3'=>'c','4'=>'d','5'=>'e','6'=>'f','7'=>'g','8'=>'h','9'=>'i','10'=>'j','11'=>'k','12'=>'l','13'=>'m','14'=>'n','15'=>'o');
            $front_pic = array_rand($a,1);
            $this->assign('front_pic',$front_pic);
            $id = input('id');
            $msg = ActivityModel::get($id);
           if($msg) {
               $msg['start_time'] = date('Y-m-d', $msg['start_time']);
               $msg['end_time'] = date('Y-m-d', $msg['end_time']);
           }
            $this->assign('info', $msg);
            return $this->fetch();
        }
    }
    /*报名记录*/
    public function recordinfo()
    {
        $id = input('id');
        $search = input('search');
        $map=[
            'activity_id' => $id,
            'status'=>['neq',-1]
        ];

        $Commentlist = ActivityComment::where($map)->paginate(12,false,['query' => request()->param()]);
        int_to_string($Commentlist, array('status' => array(0 => "未审核", 1 => "审核成功", 2=>"审核失败")));

        //echo ExchangeRecord::getLastSql();
        $this->assign('activity_id',$id);
        $this->assign("list", $Commentlist);
        $this->assign('search',$search);

        return $this->fetch();
    }



    /*删除报名记录*/
    public function delSign()
    {
        $id = input('id/a');

        $data = [
            'status' => -1
        ];
        $recordinfo = ActivityComment::where(['id'=>array('in',$id)])->update($data);
        if ($recordinfo) {

            return $this->success('删除成功');
        } else {
            return $this->error('删除失败','',ExchangeRecord::getLastSql());
        }

    }

    /**
     * 删除商品功能
     */
    public function del(){
        $id = input('id/a');
        $data['status'] = '-1';
        $info = ActivityModel::where(['id'=>array('in',$id)])->update($data);
        if($info) {
            return $this->success("删除成功");
        }else{
            return $this->error("删除失败");
        }
    }

    //导出
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
}