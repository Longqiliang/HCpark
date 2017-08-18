<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/8/16
 * Time: 下午9:46
 */

namespace app\common\model;


use think\Model;

class CarparkRecord extends  Model
{
    public  function  findService(){

       return $this->hasOne('CarparkService','id','carpark_id');


    }

}