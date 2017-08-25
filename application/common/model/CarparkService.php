<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/8/16
 * Time: 下午9:46
 */

namespace app\common\model;


use think\Model;

class CarparkService  extends Model
{
public  function  user(){


    return $this->hasOne('WechatUser','userid','user_id');
}


}