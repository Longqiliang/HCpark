<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

return [
    /* 模块配置 */
    // 默认模块名
    'default_module'         => 'admin',
    // 禁止访问模块
    'deny_module_list'       => ['common'],
    // 默认控制器名
    'default_controller'     => 'Index',
    // 默认操作名
    'default_action'         => 'index',
    // 默认验证器
    'default_validate'       => '',
    // 默认的空控制器名
    'empty_controller'       => 'Error',
    // 操作方法后缀
    'action_suffix'          => '',
    // 自动搜索控制器
    'controller_auto_search' => false,

    'http_exception_template'    =>  [
        // 定义404错误的重定向页面地址
        404 =>  APP_PATH.'admin/view/base/404.html',
        // 还可以定义其它的HTTP status
        401 =>  APP_PATH.'admin/view/base/401.html',
        500 =>  APP_PATH.'admin/view/base/500.html',
    ],

    /* 分页自定义 */
    'paginate' => [
        'type'     => '\org\Page',
        'var_page' => 'page',
        'list_rows'=> 12
    ],

    /* 文件上传相关配置 */
    'file' => [
        'ext'       => 'jpg,gif,png,jpeg,zip,rar,tar,gz,7z,doc,docx,txt,xml,bz2,tar.bz2', //允许上传的文件后缀
        'size'      => 200*1024*1024,
        'type'      => '',
        'path'  => 'public/uploads/file/', //保存根路径
        'subName'   => ['date', 'Y-m-d']
    ],

    // 微信公众号
    'pay' => [
        'appid' => 'ww61224724ee102fbc',
        'agentid' => '3010046',
        'appsecret' => 'GcDM17DNMifgH7nMCGQ53qADpyyb9beSGvGGoWaK1HI'
    ],
    'weixinpay'       => [
        'appid'       => 'ww61224724ee102fbc', // 微信支付appid
        'mchid'       => '1486387062', // 微信支付mchid 商户收款账号
        'key'         => 'e10adc3949ba59abbe56e057f20f883t', // 微信支付key
        'appsecret'   => 'GcDM17DNMifgH7nMCGQ53qADpyyb9beSGvGGoWaK1HI', // 公众帐号secert (公众号支付专用)
        'notify_url' => 'http://pointsmall.0571ztnet.com/index/wechat/notify', // 接收支付状态的连接
    ],
    /* UC用户中心配置 */
    'uc_auth_key' => '(.t!)=JTb_OPCkrD:-i"QEz6KLGq5glnf^[{p;je',
    /* 短信发送 */
    'Ucpaas' => [
        'accountSid' => '',
        'accountToken' => '',
        'appId' => ''
    ],

    'log' => [
        'type'                  =>  'socket',
        'host'                  =>  'localhost',
        'show_included_files'   =>  true,
        'force_client_ids'      =>  ['game_1259'],
        'allow_client_ids'      =>  ['game_1259'],
    ],

];
