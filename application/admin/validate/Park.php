<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/8/17
 * Time: 下午8:25
 */
namespace app\admin\validate;

use think\Validate;

class  Park extends Validate
{
    protected $rule = [
        'name' =>  'require',
        'property_phone'=>'require',
        'ailpay_user'=>'require',
        'payment_alipay'=>'require',
        'bank_user'=>'require',
        'payment_bank'=>'require',
    ];

    protected $message = [
        'name' =>  '园区名称必须添加',
        'property_phone'=>'请输入物业咨询电话',
        'ailpay_user'=>'请输入支付宝用户',
        'payment_alipay'=>'请输入缴费支付宝账号',
        'bank_user'=>'请输入银行用户',
        'payment_bank'=>'请输入银行卡号',
    ];



}