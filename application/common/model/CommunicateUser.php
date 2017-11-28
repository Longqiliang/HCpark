<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/8/31
 * Time: 下午2:23
 */

namespace app\common\model;


use think\Db;
use think\Model;

class CommunicateUser extends  Model
{
  public  function  user(){

      return $this->hasOne('WechatUser','userid','user_id');

  }
    public  function  group(){

        return $this->hasOne('CommunicateGroup','id','group_id');

    }

    public static  function  getNumforUndone(){
        $parkid = session('user_auth')['park_id'];
        $num = Db::table('tb_communicate_group')
            ->alias('g')
            ->join('tb_communicate_user u', 'g.id=u.group_id')
            ->where('u.status',1)
            ->where('g.status',1)
            ->where('g.park_id',$parkid)
            ->count();
        $a = Db::getLastSql();
        return $num;
    }





}