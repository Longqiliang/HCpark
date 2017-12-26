<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/12/4
 * Time: 下午4:16
 */

namespace app\admin\controller;

use app\common\model\CopyrightArt;
use app\common\model\CopyrightSoft;
use app\common\model\CopyrightSoftwrite;
use app\common\behavior\MyPaginate;
use app\common\behavior\Service;


class Copyright extends Admin
{
    //版权三个页面
    public function index()
    {   $copysoftwrite = new CopyrightSoftwrite();
        $copyart = new CopyrightArt();
        $copysoft = new CopyrightSoft();
        $paginate = new MyPaginate();
        $copyartlist = $copyart->getCoyprightbyParkid();
        $copysoftwritelist = $copysoftwrite->getCoyprightbyParkid();
        $copysoftlist = $copysoft->getCoyprightbyParkid();
        $num2 = $copysoft->getNumforUndone();
        $num3 = $copysoftwrite->getNumforUndone();
        $num1 = $copyart->getNumforUndone();
        $list = array_merge($copyartlist, $copysoftlist,$copysoftwritelist);
        int_to_string($list,$map=array('type'=>array(1=>'艺术作品',2=>'软著登记',3=>'软著撰写')));
        $list =  list_sort_by($list,'create_time','desc');
        $list = list_sort_by($list,'status','asc');
        $list = $paginate->paginate2($list,12,false,['query' => request()->param()]);
        $this->assign('count',$num1+$num2+$num3);
        $this->assign('list', $list);
        //echo json_encode($list);
        return $this->fetch();

    }

    //详情页
     public  function  info(){
         $id = input('id');
         $type = input('type');
         if ($type == 1) {
             //艺术作品
             $mode = new CopyrightArt();
         } elseif ($type == 2) {
             //软著登记
             $mode = new CopyrightSoft();
         } elseif ($type == 3) {
             //软著撰写
             $mode = new CopyrightSoftwrite();
         }
          $data = $mode->where('id',$id)->find();
         if ($type == 1) {
             //艺术作品
             $data['product_img']=json_decode($data['product_img']);
         }
          switch ($data['status']){
              case 0:$data['status_text']="未审核"; break;
              case 1:$data['status_text']="审核成功"; break;
              case 2:$data['status_text']="审核失败"; break;
              case -1:$data['status_text']="删除"; break;
          }
          $this->assign('info',$data);
          $this->assign('type',$type);
          return $this->fetch();
     }

    public  function  topDel(){
        $data = input('');
        $item = array();
        foreach ($data['ids'] as $id) {
            $a = json_decode($id);
            switch ($a[0]) {
                case  1:
                    $item = $a[1];
                    $result = CopyrightArt::where('id',  $item)->update(['status' => -1]);
                    break;
                case  2:
                    $item = $a[1];
                    $result = CopyrightSoft::where('id',  $item)->update(['status' => -1]);
                    break;
                case  3:
                    $item = $a[1];
                    $result = CopyrightSoftwrite::where('id', $item)->update(['status' => -1]);
                    break;
            }
        }
        if ($result) {
            return $this->success('删除成功');
        } elseif (!$result) {
            return $this->error('删除失败');
        }

    }


    //删除
    public function del()
    {
        $id = input('ids/a');
        $type = input('type');
        if ($type == 1) {
            //艺术作品
            $mode = new CopyrightArt();
        } elseif ($type == 2) {
            //软著登记
            $mode = new CopyrightSoft();
        } elseif ($type == 3) {
            //软著撰写
            $mode = new CopyrightSoftwrite();
        }
        $res = $mode->where(['id' => ['in', $id]])->Update(['status' => -1]);

        if ($res) {
            return $this->success("删除成功");
        } else {
            return $this->error("删除失败", '', $mode->getError());
        }
    }

    //审核
    public function check()
    {
        $id = input('id');
        $type = input('type');
        $is_pass = input('pass');
        $reply = input('reply');
        $parkid  =session("user_auth")['park_id'];
       $service = new Service();
        if($is_pass==2&&empty($reply)){
            return $this->error("审核失败时，园区回复一定要填");
        }
        $name ="";
        if ($type == 1) {
            //艺术作品
            $name="艺术作品";
            $mode = new CopyrightArt();
        } elseif ($type == 2) {
            //软著登记
            $name="软著登记";
            $mode = new CopyrightSoft();
        } elseif ($type == 3) {
            //软著撰写
            $name="软著撰写";
            $mode = new CopyrightSoftwrite();
        }
        $status = $is_pass == 1 ? 1 : 2;

        $res = $mode->where('id', $id)->update(['status' => $status, 'end_time' => time(), 'reply' => $reply]);
        $info =$mode->where('id',$id)->find();
        if ($res) {

            if($status==1){

                $message = [
                    "title" => "版权登记提示",
                    "description" => "您的".$name."申请园区已确认，请您携带相关材料前往希垦科技园A幢2楼园区知识产权服务中心办理。",
                    "url" => 'https://' . $_SERVER['HTTP_HOST'] . '/index/service/historyDetailCompany/appid/22/can_check/yes/type/'.$type.'/id/' . $id,
                ];
                //推送给用户
               $re = $service->commonSend(4, $message, $info['userid'], 22,$parkid);

            }else{

                $message = [
                    "title" => "版权登记提示",
                    "description" => "您的".$name."申请园区审核失败，请您核对信息后重新提交。",
                    "url" => 'https://' . $_SERVER['HTTP_HOST'] . '/index/service/historyDetailCompany/appid/22/can_check/no/type/'.$type.'/id/' . $id,
                ];

                if(!empty($reply)){
                    $message['description'] .= '备注：'.$reply;
                }
                //推送给用户
               $re = $service->commonSend(4, $message,  $info['userid'], 22,$parkid);


            }
            return $this->success('成功','copyright/index');
        } else {
            return $this->error('失败');
        }


    }


}