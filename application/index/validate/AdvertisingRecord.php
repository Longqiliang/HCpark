<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/8/18
 * Time: 下午5:33
 */

namespace app\index\validate;


use think\Validate;

class AdvertisingRecord extends Validate
{
    protected $rule = [


        'create_user'=>'require',
        'service_id'=>'require',

        'order_time'=>'require',

    ];

    protected $message = [
        'create_user.require' => '创建用户必须添加',
        'service_id.require' => '所选服务（service_id）缺失',
        'payment_voucher.require' => '办理时长必须添加',
        'order_time.require' => '公司名称必须添加',

    ];



}