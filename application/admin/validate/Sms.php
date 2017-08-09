<?php
/**
 * Created by PhpStorm.
 * User: 虚空之翼
 * Date: 16/8/15
 * Time: 下午4:30
 */

namespace app\admin\validate;

use think\Validate;

class Sms extends Validate
{
    protected $rule = [
        'to' =>  'require',
        'content'  =>  'require',
    ];

    protected $message = [
        'to' => '手机号码必须输入',
        'content' => '消息内容必须添加',
    ];
}