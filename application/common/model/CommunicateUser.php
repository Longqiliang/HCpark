<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/8/31
 * Time: 下午2:23
 */

namespace app\common\model;


use think\Model;

class CommunicateUser extends  Model
{
  public  function  user(){

      return $this->hasOne('WechatUser','userid','user_id');

  }
    public  function  group(){

        return $this->hasOne('CommunicateGroup','id','group_id');

    }
}