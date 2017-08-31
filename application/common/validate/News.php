<?php
namespace app\common\validate;
use think\Validate;

/**
 * Created by PhpStorm.
 * User: 虚空之翼
 * Date: 2017/5/9
 * Time: 上午9:23
 */
class News extends Validate
{
    protected $rule = [
        'title' =>  'require|max:100',
        'source' => 'require|max:50',
        'content'  =>  'require|min:20',
    ];

    protected $message = [
        'title.require' => '标题名称必须添加',
        'title.max' => '标题最大50个字符',
        'source.require' => '发布人名称必须添加',
        'source.max' => '发布人最大20个字符',
        'content.min' => '您发布的字数较少，请重新添加',
    ];
}