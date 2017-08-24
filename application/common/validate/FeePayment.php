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
        'expiration1' => ['regex'=>'/\d{4}/i']

    ];

    protected $message = [
        'fee' => '金额必须添加',
        'expiration1.regex' => '时间格式不正确',
    ];


}