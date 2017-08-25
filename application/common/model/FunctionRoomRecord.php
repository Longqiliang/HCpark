<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/8/25
 * Time: 下午6:01
 */

namespace app\common\model;


use think\Model;

class FunctionRoomRecord extends  Model
{

    public  function  findUser(){

        return $this->hasOne('WechatUser','userid','create_user');
    }



}