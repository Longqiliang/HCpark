<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/8/25
 * Time: 下午2:15
 */

namespace app\common\model;


use think\Model;

class AdvertisingService  extends  Model
{
     public  function  findPark(){



         return $this->hasOne('WechatDepartment','id','park_id');
     }



}