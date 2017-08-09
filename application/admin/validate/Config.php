<?php
/**
 * Created by PhpStorm.
 * User: 虚空之翼
 * Date: 16/7/29
 * Time: 上午9:52
 */

namespace app\admin\validate;

use think\Validate;

class Config extends Validate
{
    protected $rule = [
        'name'  =>  'require|unique:config',
        'title' =>  'require',
    ];

    protected $message = [
        'username.require'  =>  '标识不能为空',
        'username.unique'  =>  '标识已经存在',
        'title'  =>  '名称不能为空',
    ];
}