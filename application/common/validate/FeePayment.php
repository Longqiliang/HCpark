<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/8/23
 * Time: 下午4:29
 */
namespace app\common\validate;
use think\Validate;
class FeePayment extends Validate
{
    protected $rule = [
        'fee' =>  'require',
        'expiration_time' => "require|regex:\d{4}\-\d{2}\-\d{2}",
        'images' => 'require',

    ];

    protected $message = [
        'fee' => '请输入缴费金额',
        'expiration_time.require'=>"请添加到期时间",
        'expiration_time.regex' => '请输入正确时间格式，如xxxx-xx-xx',
        'images.require' =>"请选择收费单据",
    ];

}