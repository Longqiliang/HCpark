<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/9/6
 * Time: 下午8:30
 */

namespace app\common\model;


use think\Model;

class MerchantsDiary extends  Model
{


    protected  $type=[

        'create_time'=>'strtotime'
    ];

    public  function  user(){

        return $this->hasOne('WechatUser','userid','user_id');

    }


}