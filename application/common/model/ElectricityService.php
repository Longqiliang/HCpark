<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/8/25
 * Time: 下午1:26
 */

namespace app\common\model;


use think\Model;

class ElectricityService extends  Model
{
    public  function  user(){


        return $this->hasOne('WechatUser','userid','user_id');
    }

}