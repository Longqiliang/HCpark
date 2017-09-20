<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/9/4
 * Time: 上午9:37
 */

namespace app\common\model;

use think\Model;

class PeopleRent extends Model
{

  public function  roominfo(){


      return $this->hasOne('ParkRent','id','rent_id');


  }



}