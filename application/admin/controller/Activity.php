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
use app\common\behavior\Service;
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
            'status' => array(0 => "活动取消",1=>'预报名',2=>'开始报名'),
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
            if(empty($data['name'])){
                return  $this->error('活动名字必填');
            }
            if($data['start_time']==0){
              return  $this->error('开始时间未填');
            }
            $data['park_id'] = $park_id;
            if (empty($data['id'])) {
                unset($data['id']);
                $info = $activity->save($data);
            }else{
                $info = $activity->save($data,['id'=>$data['id']]);
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
        $data = input('');
        $list = ActivityComment::where(['activity_id'=>$data['id'],'status'=>1])->select();
        foreach ($list as $value){
            $value['activity_name']=isset($value->activity->name)?$value->activity->name:"";
            $value['start_time']=isset($value->activity->start_time)?$value->activity->start_time:"";
        }
        $excel = new PHPExcel();
        $letter = array('A', 'B', 'C', 'D', 'E', 'F', 'F', 'G');
        $celltitle = [
            '用户编号', '用户姓名', '联系电话', '所属公司（选填）', '备注信息', '报名时间','活动名称', '活动时间'
        ];
        foreach ($list as $key => $value) {
            $cellData[$key] = [$value['userid'], $value['name'], $value['mobile'], $value['department'], $value['remark'], $value['create_time'],$value['activity_name']  ,date('Y-m-d',$value['start_time'])];
        }

        $excel->getActiveSheet()->getRowDimension(1)->setRowHeight(30);
        $excel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $excel->getActiveSheet()->getColumnDimension('D')->setWidth(35);
        $excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $excel->getActiveSheet()->getColumnDimension('G')->setWidth(16);
        $excel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
        $excel->getActiveSheet()->getRowDimension(1)->setRowHeight(35);
        $excel->getActiveSheet()->getRowDimension(2)->setRowHeight(22);
        $excel->getActiveSheet()->getRowDimension(3)->setRowHeight(20);
        //设置字体样式
        $excel->getActiveSheet()->getStyle('A1:G1')->getFont()->setName('黑体');
        $excel->getActiveSheet()->getStyle('A1:G1')->getFont()->setSize(20);
        $excel->getActiveSheet()->getStyle('A1:G1')->getFont()->setBold(true);
        $excel->getActiveSheet()->getStyle('A1:G1')->getFont()->setBold(true);
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
        header('Content-Disposition:attachment;filename="活动报名导出表格.xls"');
        header("Content-Transfer-Encoding:binary");
        $write->save('php://output');

    }
    //活动推送
    public  function  send(){
        $data = input('');
        $activity = new ActivityModel();
        $service = new Service();
        $info = $activity->where('id',$data['id'])->find();
        $des = msubstr(str_replace('&nbsp;', '', strip_tags($info['activity_description'])), 0, 36);
        $map = [
                    'title' => $info['name'],
                    'description' => $des,
                     'url'=>'https://' . $_SERVER['HTTP_HOST'] . '/index/activity/detail/id/' . $data['id'],
                    'picurl' => empty($data['front_cover'])
                        ? 'https://' . $_SERVER['HTTP_HOST'] .'/index/images/news/news.jpg'
                        : 'https://' . $_SERVER['HTTP_HOST'] . $data['front_cover']
        ];
        $result =$service->sendNews2(Config('activity'),$map);
        if ($result['errcode'] == 0) {
            ActivityModel::where('id', input('id'))->update(['is_send' => 1]);
            return $this->success('推送成功', 'Activity/index');
        } else {
            return $this->error('推送失败');
        }

    }

    public  function  change(){
        $data= input('');
        $activity = new ActivityModel();
        //开始报名
        if($data['type']==1){
         $result = $activity->where('id',$data['id'])->update(['status'=>2]);
         if($result){
             return $this->success('开始报名成功');

         }else {
             return $this->error("开始报名失败");

         }
        }
        //取消报名
        elseif ($data['type']==2){
            if(empty($data['reply'])){
                return $this->error('取消原因未填');
            }
            $map=['status'=>0,
                  'reply'=>$data['reply']
                ];
            $result = $activity->where('id',$data['id'])->update($map);

            if($result){
                $service = new Service();
                $info =$activity->where('id',$data['id'])->find();
                $userList = isset($info->user)?$info->user:array();
               $userid = '';
                foreach ($userList as $user) {
                    if ($user['status'] == 1) {
                     $userid .="|".$user['userid'];
                    }
                }
                $data = [
                    'title' => "园区活动提示",
                    'description' => "您报名参加的" . $info['name'] . "因" . $info['reply'] . "原因取消，感谢您对园区活动的大力支持，请继续关注最新园区活动动态。",
                    'url' => 'https://' . $_SERVER['HTTP_HOST'] . '/index/activity/detail/id/' . $info['id'],
                ];
                $service->sendActivityMessage($data,$userid);
                return $this->success('取消报名成功');

            }else {
                return $this->error("取消报名失败");

            }
        }
    }




}