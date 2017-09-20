<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/9/2
 * Time: 上午10:24
 */
namespace app\common\model;

use think\Model;

class ParkRent extends Model{


  public  function  room(){

      return $this->hasOne('ParkRoom','id','room_id');
  }
}