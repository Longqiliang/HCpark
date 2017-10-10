<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/10/10
 * Time: 下午1:06
 */

namespace app\index\model;


use think\Model;

class OperationalAuthority extends Model
{
    public  function  user(){


        return $this->hasOne('WechatUser','userid','userid');
    }

}