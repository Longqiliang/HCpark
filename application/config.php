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
    'upload_drive'=>'local',
    'upload_local_config'=>array(),

    /* 文件上传相关配置 */
    'download_upload' => array(
        'mimes'    => '', //允许上传的文件MiMe类型
        'maxSize'  => 20*1024*1024, //上传的文件大小限制 (0-不做限制)
        'exts'     => 'jpg,gif,png,jpeg,zip,rar,tar,gz,7z,doc,docx,txt,xml,mp4,avi,wav,remvb,', //允许上传的文件后缀
        'autoSub'  => true, //自动子目录保存文件
        'subName'  => array('date', 'Y-m-d'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
        'rootPath' => './uploads/download/', //保存根路径
        'savePath' => '', //保存路径
        'saveName' => array('uniqid', ''), //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
        'saveExt'  => '', //文件保存后缀，空则使用原后缀
        'replace'  => false, //存在同名是否覆盖
        'hash'     => true, //是否生成hash编码
        'callback' => false, //检测文件是否存在回调函数，如果存在返回文件信息数组
    ),

    /* 图片上传相关配置 */
    'picture_upload' => array(
        'mimes'    => '', //允许上传的文件MiMe类型
        'maxSize'  => 10*1024*1024, //上传的文件大小限制 (0-不做限制)
        'exts'     => 'jpg,gif,png,jpeg', //允许上传的文件后缀
        'autoSub'  => true, //自动子目录保存文件
        'subName'  => array('date', 'Y-m-d'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
        'rootPath' => 'uploads/picture/', //保存根路径
        'savePath' => '', //保存路径
        'saveName' => array('uniqid', ''), //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
        'saveExt'  => '', //文件保存后缀，空则使用原后缀
        'replace'  => false, //存在同名是否覆盖
        'hash'     => true, //是否生成hash编码
        'callback' => false, //检测文件是否存在回调函数，如果存在返回文件信息数组
    ),

    /* 编辑器图片上传相关配置 */
    'editor_upload' => array(
        'mimes'    => '', //允许上传的文件MiMe类型
        'maxSize'  => 10*1024*1024, //上传的文件大小限制 (0-不做限制)
        'exts'     => 'jpg,gif,png,jpeg', //允许上传的文件后缀
        'autoSub'  => true, //自动子目录保存文件
        'subName'  => array('date', 'Y-m-d'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
        'rootPath' => 'uploads/editor/', //保存根路径
        'savePath' => '', //保存路径
        'saveName' => array('uniqid', ''), //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
        'saveExt'  => '', //文件保存后缀，空则使用原后缀
        'replace'  => false, //存在同名是否覆盖
        'hash'     => true, //是否生成hash编码
        'callback' => false, //检测文件是否存在回调函数，如果存在返回文件信息数组
    ),

    // 微信公众号
    /* 企业配置
        *  同步通讯录
        */
    'party' => array(
        'login' => 'http://xk.0519ztnet.com/index/wechat/login',
//        'token' => 'RMRUYhgJh7C',
//        'encodingaeskey' => 'XO7KtGSIpsnGPR24x3UmfTnLXSmEfogGhmRqUkoefNj',
        'appid' => 'ww68db00a56b949cff',
        'appsecret' => 'pgdv-joifw5SlO7UCsUT8bISF7SA6tFk0ERTg_dkU4g',
        'agentid' => 1000004
    ),
    'news' => [
        "AppDesc"=>"新闻",
        'appid' => 'ww68db00a56b949cff',
        'appsecret' => 'JKhsF4Xp7MNlF7SK4twq1i5ZKHEcxpXsSPtL8qF2kv4',
        'agentid' => 1000005
    ],
    'feedback' => [
        "AppDesc"=>"意见反馈",
        'appid' => 'ww68db00a56b949cff',
        'appsecret' => 'PJPyUUO1sTesUoyR9hhukvGVUZzQnMUDlFZNk16tcAU',
        'agentid' => 1000006
    ],


    'reply' => array(
        'AppDesc'=>"意见回复",
        'appid' => 'ww68db00a56b949cff',
        'appsecret' => 'aKasEURFVx7QvZVOux4wsd6McsyJGC0iXEKun1KbpNM',
        'agentid' => 1000007
    ),
    'addressbook' => array(
        "AppDesc"=>"通讯录",
        'appid' => 'ww68db00a56b949cff',
        'appsecret' => '4Y_vfF2ovs1Bc_UmMsvLEZh3XQxczHdgOAUSzghr2BA',
        'agentid' => 1000004
    ),




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
    'app_debug'=>true
];
