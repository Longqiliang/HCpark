<?php
/**
 * Created by PhpStorm.
 * User: 虚空之翼
 * Date: 16/7/4
 * Time: 下午5:41
 */

namespace app\admin\validate;

use think\Validate;

class AuthGroup extends Validate
{
    protected $rule = [
        'title' =>  'require',
    ];

    protected $message = [
        'title' =>  '用户组名称必须添加',
    ];
}