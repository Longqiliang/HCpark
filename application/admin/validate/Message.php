<?php
/**
 * Created by PhpStorm.
 * User: 虚空之翼
 * Date: 16/7/5
 * Time: 上午10:16
 */

namespace app\admin\validate;

use think\Validate;

class Message extends Validate
{
    protected $rule = [
        'title' =>  'require',
        'content'  =>  'require',
    ];

    protected $message = [
        'title' => '标题名称必须添加',
        'content' => '消息内容必须添加',
    ];
}