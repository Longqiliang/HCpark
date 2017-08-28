<?php
/**
 * Created by PhpStorm.
 * User: shen
 * Date: 2017/8/28
 * Time: 下午7:10
 */
namespace app\common\validate;
use think\Validate;

class Feedback extends Validate
{
    protected $rule = [
        'reply'  =>  'require',
    ];

    protected $message = [

        'reply' => '回复内容必须添加',
    ];
}