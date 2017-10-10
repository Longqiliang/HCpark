<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/10/9
 * Time: 下午3:24
 */

namespace app\common\model;


use think\Model;

class OperationalAuthority extends  Model
{
  public  function  user(){


      return $this->hasOne('WechatUser','userid','userid');
  }


}