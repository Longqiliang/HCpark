<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/15
 * Time: 15:17
 */
namespace app\common\validate;

use think\Validate;

class CompanyApplication extends Validate
{
    protected $rule = [
        'name' =>  'require|max:40',
        'img' => 'require',
        'path' => 'require|max:120',
        'type' => 'require',
    ];

    protected $message = [
        'name.require' => '应用名称必须添加',
        'name.max' => '应用名称最大50个字符',
        'img' => '图片必须添加',
        'path.require' => '地址必须添加',
        'path.max' => '地址最大120个字符',
        'type' => '类型必须添加',
    ];
}