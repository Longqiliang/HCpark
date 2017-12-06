<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/12/4
 * Time: 下午4:24
 */

namespace app\common\model;


use think\Model;

class CopyrightSoftwrite extends  Model
{
 protected  $type =[

     'create_time'=>'strotrtime',
     'end_time'=>'strotrtime'
 ];

//当前园区的所有艺术
    public  function  getCoyprightbyParkid(){

        $parkid = session('user_auth')['park_id'];
        $map=[
            'park_id'=>$parkid,
            'status'=>array('neq',-1),
        ];
        $list =$this::where($map)->field('id,status,create_time,end_time,contact_staff,contact_number,3 as type ')->select();
        foreach ($list as $value){
            $value['del_type']=json_encode( [3,$value['id']]);
        }
        return $list;



    }
    //未完成数量
    public  static function  getNumforUndone(){
        $parkid = session('user_auth')['park_id'];
        $map['status'] = 0;
        $map['park_id']=$parkid;
        $count = CopyrightSoftwrite::where($map)->count();
        return $count;

    }

}