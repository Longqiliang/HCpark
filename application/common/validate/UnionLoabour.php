<?php
/**
 * Created by PhpStorm.
 * User: shen
 * Date: 2017/8/28
 * Time: 下午5:25
 */
namespace app\common\validate;
use think\Validate;

class UnionLoabour extends Validate
{
    protected $rule = [
        'title' =>  'require',
        'source' => 'require',
        'front_cover' =>  'require',
        'type' => 'require',
        'contact' =>  'require',
        'mobile' => 'require',
        'address' =>  'require',
        'office_time_start' => 'require',
        'office_time_end' => 'require',
    ];

    protected $message = [
        'title.require' => '工会名称必须添加',
        'source.require' => '发布人名称必须添加',
        'front_cover.require' => '封面图片必须添加',
        'type.require' => '工会类别必须添加',
        'contact.require' => '联系人必须添加',
        'mobile.require' => '联系方式必须添加',
        'address.require' => '具体地址必须添加',
        'office_time_start.require' => '办公开始时间必须添加',
        'office_time_end.require' => '办公结束时间必须添加',
    ];
}