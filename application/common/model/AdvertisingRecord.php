<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/8/25
 * Time: 下午2:16
 */

namespace app\common\model;


use think\Model;

class AdvertisingRecord extends  Model
{
    public  function  findUser(){

        return $this->hasOne('WechatUser','userid','create_user');
    }



}