<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/9/1
 * Time: 下午4:30
 */

namespace app\index\model;


use think\Model;

class MerchantsDiary extends  Model
{
    protected $insert=[

        'update_time'=>NOW_TIME

    ];

    protected  $type=[
        'update_time'=>'strtotime',
        'create_time'=>'strtotime'
    ];

  public  function  user(){

      return $this->hasOne('WechatUser','userid','user_id');

  }
}