<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/15
 * Time: 10:16
 */
namespace app\index\validate;

use think\Validate;

class BroadbandPhone extends Validate
{
    protected $rule = [
        'address' =>  'require',
        'business' => 'require',
        'business_time' => 'require',
        'company' => 'require',
        'people' => 'require',
        'mobile' => 'require',
        'remark' => 'max:200',
    ];

    protected $message = [
        'address.require' => '办公地点必须添加',
        'business.require' => '办理业务必须添加',
        'business_time.require' => '办理时长必须添加',
        'company.require' => '公司名称必须添加',
        'people.require' => '联系人员必须添加',
        'mobile.require' => '联系方式必须添加',
        'remark.max' => '备注信息不能超过200字符',
    ];
}


