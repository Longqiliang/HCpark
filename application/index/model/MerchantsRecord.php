<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/9/1
 * Time: 下午4:28
 */

namespace app\index\model;


use think\Model;

class MerchantsRecord extends Model
{
    protected  $type =[

        'merchants_date'=>'strtotime',

    ];


    public function merchantsCompany()
    {


        return $this->hasOne('MerchantsCompany', 'id', 'merchants_id');
    }
}