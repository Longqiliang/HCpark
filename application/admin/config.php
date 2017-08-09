<?php
return [
    /* 用户相关设置 */
    'user_max_cache'     => 1000, //最大缓存用户数
    'user_administrator' => 1,    //管理员用户ID

//    'develop_mode' => 1,
    'admin_allow_ip' => '',

    'app_trace' =>  false,
    'trace' =>[
        //支持Html,Console 设为false则不显示
        'type'  =>  'html',
    ],

    'session_prefix' => 'ad_admin',  //session前缀
    'cookie_prefix'  => 'ad_admin_', // Cookie前缀 避免冲突
    'var_session_id' => 'session_id',//修复uploadify插件无法传递session_id的bug

    'Ucpaas' => [
        'accountSid' => '',
        'accountToken' => '',
        'appId' => ''
    ],

];