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
        $list = list_sort_by($list,'create_time','desc');
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
        if($is_pass==2&&empty($reply)){
            return $this->error("审核失败时，园区回复一定要填");
        }
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
        $status = $is_pass == 1 ? 1 : 2;

        $res = $mode->where('id', $id)->update(['status' => $status, 'end_time' => time(), 'reply' => $reply]);

        if ($res) {
            return $this->success('成功');
        } else {
            return $this->error('失败');
        }


    }


}