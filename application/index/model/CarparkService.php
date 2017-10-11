<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/8/16
 * Time: 下午8:44
 */

namespace app\index\model;


use think\Model;

class CarparkService extends Model
{


    protected $type = [

        'create_time' => 'strtotime'

    ];

    public function findRecord()
    {


        return $this->hasMany('CarparkRecord', 'carpark_id', 'id');


    }
    public  function  user(){

        return $this->hasOne('WechatUser','userid','user_id');

    }


}