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
    public  function  index(){
        $CardparkRecord=new CarparkRecord();
        $map['status']=array('neq',-1);
        $list= $CardparkRecord->where($map)->order('status asc')->paginate();
        int_to_string($list, array(
            'status' => array(0=>'审核中',1=>'审核通过',2=>'审核失败'),
         'type' => array(1=>'新卡办理',2=>'旧卡办理')
        ));
        $re= $CardparkRecord->where('status',0)->select();
        $data['data']=$list;
        $data['num']=count($re);
        $this->assign('list',$data);


        return $this->fetch();

     }

    public  function  check(){
        $CardparkRecord=new CarparkRecord();
        $id=input(get.id);
        $info =$CardparkRecord->where('id',$id)->find();
        $this->assign('info',$info);
        return $this->fetch();




    }


}