<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/11/15
 * Time: 下午2:04
 */

namespace app\common\model;


use think\Model;

class ExchangeRecord extends Model
{

    protected $type = [
        'create_time' => 'strtotime',
    ];

    public function user()
    {

        return $this->hasOne('WechatUser', 'userid', 'userid');

    }

    public function productinfo()
    {
        return $this->hasOne('ExchangeProduct', 'id', 'product_id');
    }

}