<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/8/25
 * Time: 下午1:26
 */

namespace app\common\model;


use think\Model;

class ElectricityRecord extends Model
{
    public  function  findService(){

        return $this->hasOne('ElectricityService','id','service_id');


    }
}