<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/11/17
 * Time: 上午9:16
 */

namespace app\common\model;


use think\Model;

class TrademarkInquire extends Model
{
    protected $type = [
        'create_time' => 'strtotime',
        'end_time' => 'strtotime'
    ];


    public function user()
    {

       return  $this->hasOne('WechatUser', 'userid', 'userid');

    }


}