<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/8/22
 * Time: 上午10:13
 */

namespace app\index\model;


use think\Model;

class ElectricityService extends  Model
{
    protected $dateFormat="Y-m-d";
    public  function  findRecord(){


        return $this->hasMany('ElectricityRecord','service_id','id')->field('id,type,money,status,create_time');


    }

    public  function  user(){

        return $this->hasOne('WechatUser','userid','user_id');

    }


}