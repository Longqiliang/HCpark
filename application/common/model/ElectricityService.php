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

    public static function getNumforUndone()
    {
        $parkid = session('user_auth')['park_id'];
        $num = ElectricityService::where(['status' => 0, 'park_id' => $parkid])->count();
        return $num;

    }


}