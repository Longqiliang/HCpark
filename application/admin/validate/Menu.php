<?php
/**
 * Created by PhpStorm.
 * User: 虚空之翼 <183700295@qq.com>
 * Date: 16/5/9
 * Time: 10:11
 */
namespace app\admin\validate;

use think\Validate;

class Menu extends Validate
{
    protected $rule = [
        'title' =>  'require',
        'url'  =>  'require',
    ];

    protected $message = [
        'title' =>  '标题名称必须添加',
        'url'  =>  'URL必须添加',
    ];

}