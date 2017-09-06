<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/8/31
 * Time: 上午9:25
 */

namespace app\index\model;


use think\Model;

class CommunicateUser extends  Model
{
    protected $insert = [
        'create_time' => NOW_TIME,
    ];

    protected  $type=[

       'create_time'=>'strtotime'
    ];

    public  function  group(){

        return $this->hasOne('CommunicateGroup','id','group_id');

    }
    public  function  user(){

        return $this->hasOne('WechatUser','userid','user_id');

    }
}