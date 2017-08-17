<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/15
 * Time: 10:16
 */
namespace app\index\validate;

use think\Validate;

class WaterService extends Validate
{
    protected $rule = [
        'address' =>  'require',
        'number' => 'require',
        'name' => 'require',
        'mobile' => 'require',
    ];

    protected $message = [
        'address.require' => '送水地点必须添加',
        'number.require' => '送水桶数必须添加',
        'name.require' => '联系人员必须添加',
        'mobile.require' => '联系电话必须添加',
    ];
}