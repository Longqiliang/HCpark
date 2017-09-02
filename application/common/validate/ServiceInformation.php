<?php
/**
 * Created by PhpStorm.
 * User: shen
 * Date: 2017/9/1
 * Time: 下午7:16
 */
namespace app\common\validate;

use think\Validate;

class ServiceInformation extends Validate
{
    protected $rule = [
        'title' =>  'require',
        'source' => 'require',
        'content' => 'require|min:12',
    ];

    protected $message = [
        'title.require' => '服务信息标题必须添加',
        'source.require' => '服务信息发布人必须添加',
        'content.min' => '服务信息内容必须添加',
    ];
}