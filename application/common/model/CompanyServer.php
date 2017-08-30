<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/8/16
 * Time: 下午2:38
 */
namespace app\common\model;

use think\Model;

class CompanyServer extends Model
{
    public function  parkid(){

        return $this->hasOne('CompanyApplication','app_id','app_id');

    }

}