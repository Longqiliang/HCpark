<?php
/**
 * Created by PhpStorm.
 * User: shen
 * Date: 2017/8/28
 * Time: 下午3:58
 */
namespace app\common\validate;
use think\Validate;

class Union extends Validate
{
    protected $rule = [
        'title' =>  'require|max:100',
        'source' => 'require|max:50',
        'content'  =>  'require|min:12',
        'front_cover'  =>  'require',
    ];

    protected $message = [
        'title.require' => '标题名称必须添加',
        'title.max' => '标题最大50个字符',
        'source.require' => '发布人名称必须添加',
        'source.max' => '发布人最大20个字符',
        'content.min' => '消息内容必须添加',
        'front_cover.require' => '封面图片必须添加',
    ];
}