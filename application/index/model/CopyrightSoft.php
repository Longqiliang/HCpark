<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/12/6
 * Time: 上午10:07
 */

namespace app\index\model;

use app\index\controller\Service;
use think\Model;

class CopyrightSoft extends  Model
{

    protected  $insert =[
        'create_time'=> NOW_TIME,
        'status'=>0,
    ];

    protected  $type =[

        'create_time'=>'strotrtime',
        'end_time'=>'strotrtime'
    ];




    //该园区所有艺术记录
    public function getCoypright($type)
    {
        $user = session('userId');
        $parkid = session('park_id');
        if ($type == 1) {
            $map = [
                'userid' => $user,
                'status' => array('neq', -1),
            ];
        } else {
            $map = [
                'park_id' => $parkid,
                'status' => array('neq', -1),
            ];
        }
        $list = $this->where($map)->field('id,status,create_time,end_time,contact_staff,contact_number,2 as type ')->select();
        int_to_string($list,$map=array('type'=>array(1=>'艺术作品',2=>'软著登记',3=>'软著撰写'),'status'=>array(0=>'审核中',1=>'审核成功',2=>'审核失败')));
        return $list;


    }




//当前用户的所有艺术
    public  function  copyHistory(){
        $park_id =session('park_id');
        $userid = session('userId');
        $map=[
            'userid'=>$userid,
            'park_id'=>$park_id
            ];
        $list =$this->where($map)->field('software_name as name,id,status,create_time,end_time,contact_staff,contact_number,2 as type ')->select();

        return $list;



    }

    //审核通过／不通过
    public function check($type, $id, $data)
    {
        if ($type == 1) {
            //审核通过
            $info = $this->where('id',$id)->update(['status'=>1,'end_time'=>time(),'reply'=>$data['reply']]);
            $message = [
                "title" => "版权申请提示",
                "description" => "您的软著登记版权申请园区已确认，请您携带相关材料前往希垦科技园A幢2楼园区知识产权服务中心办理，点击查看详情",
                "url" => 'https://' . $_SERVER['HTTP_HOST'] .'/index/service/historyDetailCompany/appid/22/can_check/no/type/2/id/' .$id
            ];
        } else {
            //审核不通过
            $info = $this->where('id',$id)->update(['status'=>$type,'end_time'=>time(),'reply'=>$data['reply']]);
            $message = [
                "title" => "版权申请提示",
                "description" => "您的软著登记版权申请园区审核失败，请您核对信息后重新提交，",
                "url" => 'https://' . $_SERVER['HTTP_HOST'] . '/index/service/historyDetailCompany/appid/22/can_check/no/type/2/id/' .$id
            ];
            //点击查看详情
            if(!empty($copy['reply'])){
                $message['description'] = $message['description']."备注：".$copy['reply']."点击查看详情";
            }
        }
        $service = new Service();
        $copy =$this->find($id);
        if($info){
            //推送
            //审核成功推用户
            $service->commonSend(4, $message,$copy['userid'] , 22);
            return true;
        }else {

            return false;
        }
    }




    public  function  _checkData($data){
        if(empty($data)){
            return false;
        }
        if(!isset($data['software_name'])||!isset($data['hardware_name'])||!isset($data['software_environment'])||!isset($data['main_function'])||!isset($data['contact_staff'])||!isset($data['contact_number'])){

            return false;
        }

        if(empty($data['software_name'])||empty($data['hardware_name'])||empty($data['software_environment'])||empty($data['main_function'])||empty($data['contact_staff'])||empty($data['contact_number'])){

            return false;
        }
        return true;
    }

     //获取软著登记详情页
    public function copyHistoryDetail($id, $appid)
    {
        $info = $this::get($id);
        $app = CompanyApplication::Where('app_id', $appid)->find();
        $info['type_name'] = "软著登记";
        $info['app_name'] = $app['name'];
        $info['type_check']=2;
        return $info;
    }
}