<?php
/**
 * Created by PhpStorm.
 * User: Lxx<779219930@qq.com>
 * Date: 2016/12/20
 * Time: 14:52
 */

namespace app\admin\validate;


use think\Validate;

class CardType extends Validate {
    protected $rule = [
        'interest' => 'require',
    ];

    protected $message = [
        'interest' => '兴趣名称不能为空',
    ];
}