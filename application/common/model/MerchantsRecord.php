<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/9/6
 * Time: 下午8:29
 */

namespace app\common\model;


use think\Model;

class MerchantsRecord extends  Model
{
    protected  $type=[
        'create_time'=>'strtotime',
        'merchants_date'=>'strtotime'
    ];
    public  function  merchantsCompany(){
        return $this->hasOne('MerchantsCompany','id','merchants_id');
    }
}