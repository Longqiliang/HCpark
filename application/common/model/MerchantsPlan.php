<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/9/6
 * Time: 下午8:28
 */

namespace app\common\model;


use think\Model;

class MerchantsPlan extends Model
{


    public  function  user(){

        return $this->hasOne('WechatUser','userid','user_id');

    }

}