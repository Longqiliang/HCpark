<?php
/**
 * Created by PhpStorm.
 * User: shen
 * Date: 2017/9/2
 * Time: 上午9:56
 */
namespace app\common\validate;

use think\Validate;

class EnterpriseRecruitment extends Validate
{
    protected $rule = [
        'position' =>  'require',
        'company' => 'require',
        'source' => 'require',
        'education' => 'require',
        'experience' => 'require',
        'number' => 'require',
        'wages' => 'require',
        'content' => 'require|min:12',
        'contact' => 'require|min:12',
    ];

    protected $message = [
        'position.require' => '招聘职位必须添加',
        'company.require' => '公司名称必须添加',
        'source.require' => '发布人名称必须添加',
        'education.require' => '学历要求必须添加',
        'experience.require' => '工作经验必须添加',
        'number.require' => '招聘人数必须添加',
        'wages.require' => '提供薪资必须添加',
        'content.min' => '职位描述必须添加',
        'contact.min' => '联系方式必须添加',
    ];
}