<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/8/31
 * Time: 上午10:55
 */

namespace app\index\model;


use think\Model;

class CommunicateComment extends  Model
{
    protected $insert = [
        'create_time' => NOW_TIME,
        'status' => 1
    ];

    public function wechatuser(){

        return $this->hasOne("WechatUser","userid","user_id");
    }


    public  function  CommunicatePost(){


        return $this->hasOne('CommunicatePosts','id','target_name');
    }
}