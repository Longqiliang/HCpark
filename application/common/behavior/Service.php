<?php
/**
 * Created by PhpStorm.
 * User: 虚空之翼
 * Date: 2017/7/20 下午2:02
 */

namespace app\common\behavior;

use think\Loader;
use wechat\TPWechat;
use app\common\model\WechatDepartment;
use app\common\model\WechatTag;
use app\index\model\WechatUser;

class Service
{
    private static function sendMessage($config, $message, $toUser)
    {
        $weObj = new TPWechat($config);
        $data = [
            'touser' => $toUser,
            'agentid' => $config['agentid'],
            'msgtype' => 'text',
            'text' => [
                'content' => $message
            ]
        ];

        return $weObj->sendMessage($data);
    }

    private static function sendNews($config, $message, $toUser)
    {
        $weObj = new TPWechat($config);
        $data = [
            'touser' => $toUser,
            'agentid' => $config['agentid'],
            'msgtype' => 'news',
            'news' => [
                'articles' => [
                    [
                        'title' => $message['title'],
                        'description' => $message['description'],
                        'url' => $message['url'],
                        'picurl' => $message['picurl']
                    ]
                ]
            ]
        ];

        return $weObj->sendMessage($data);
    }


    private static function sendTextCard($config, $message, $toUser)
    {
        $weObj = new TPWechat($config);
        $data = [
            'touser' => $toUser,
            'agentid' => $config['agentid'],
            'msgtype' => 'textcard',
            'textcard' => [
                'title' => $message['title'],
                'description' => $message['description'],
                'url' => $message['url'],
            ]
        ];

        return $weObj->sendMessage($data);
    }


    /**
     * 个人中心发送文本信息
     * @param string $message 文本内容
     * @param string $toUser 发送对象，如果为空则发送给全体
     * @return array|bool
     */
    public static function sendUserMessage($message, $toUser = '@all')
    {
        $config = config('user');

        return self::sendMessage($config, $message, $toUser);
    }

    /**
     * 商城动态发送文本信息
     * @param string $message 文本内容
     * @param string $toUser 发送对象，如果为空则发送给全体
     * @return array|bool
     */
    public static function sendNewsMessage($message, $toUser = '@all')
    {
        $config = config('news');

        return self::sendMessage($config, $message, $toUser);
    }

    /**
     * 个人中心发送新闻通知
     * @param array $message 新闻数据
     * [
     *     'title' => $message['title'],
     *     'description' => $message['description'],
     *     'url' => $message['url'],
     *     'picurl' => $message['picurl']
     * ]
     * @param string $toUser 发送对象，如果为空则发送给全体
     * @return array|bool
     */
    public static function sendUserNews($message, $toUser = '@all')
    {
        $config = config('user');

        return self::sendNews($config, $message, $toUser);
    }

    /**
     * 商城动态发送新闻通知
     * @param array $message 新闻数据
     * [
     *     'title' => $message['title'],
     *     'description' => $message['description'],
     *     'url' => $message['url'],
     *     'picurl' => $message['picurl']
     * ]
     * @param string $toUser 发送对象，如果为空则发送给全体
     * @return array|bool
     */
    public static function sendNewsNews($message, $toUser = '@all')
    {
        $config = config('news');

        return self::sendNews($config, $message, $toUser);
    }



    /**
     * 个人中心发送文本卡片
     * @param array $message 新闻数据
     * [
     *     'title' => $message['title'],
     *     'description' => $message['description'],
     *     'url' => $message['url'],
     *
     * ]
     * @param string $toUser 发送对象，如果为空则发送给全体
     * @return array|bool
     */
    public static function sendPersonalMessage($message, $toUser = '@all')
    {
        $config = config('personal');

        return self::sendTextCard($config, $message, $toUser);
    }


}