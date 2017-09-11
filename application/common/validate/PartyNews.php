<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/8/28
 * Time: 下午2:54
 */
namespace app\common\validate;
use think\Validate;
class PartyNews extends Validate{

    protected $rule = [
        'title' =>  'require|max:100',
        'source' => 'require|max:50',
        'content'  =>  'require|min:12',
    ];

    protected $message = [
        'title.require' => '标题名称必须添加',
        'title.max' => '标题最大50个字符',
        'source.require' => '发布人名称必须添加',
        'source.max' => '发布人最大20个字符',
        'content.min' => '新闻内容字数过少，请继续添加',
    ];


}