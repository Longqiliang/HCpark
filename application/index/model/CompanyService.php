<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/8/15
 * Time: 下午4:26
 */

namespace app\index\model;


use think\Model;

class CompanyService extends Model
{

    public  function  user(){

        return $this->hasOne('WechatUser','userid','user_id');

    }
}