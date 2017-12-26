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
            'touser' => 15706844655,
            'agentid' => $config['agentid'],
            'msgtype' => 'text',
            'text' => [
                'content' => $message
            ]
        ];

        return $weObj->sendMessage($data);
    }

    private static function sendNews($config, $message, $toUser="@all")
    {
        $weObj = new TPWechat($config);
        $data = [
            'touser' => 15706844655,
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
            'touser' => 15706844655,
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
    public static function sendUserMessage($message, $toUser )
    {
        $config = config('user');
        $is= substr_count($message['url'], 'https');
        if($is==0){
            $message['url']= str_replace("http","https",$message['url']);
        }
        return self::sendMessage($config, $message,$toUser);
    }

    /**
     * 商城动态发送文本信息
     * @param string $message 文本内容
     * @param string $toUser 发送对象，如果为空则发送给全体
     * @return array|bool
     */
    public static function sendNewsMessage($message, $toUser )
    {
        $config = config('news');
        $is= substr_count($message['url'], 'https');
        if($is==0){
            $message['url']= str_replace("http","https",$message['url']);
        }
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
    public static function sendUserNews($message, $toUser )
    {
        $config = config('user');
        $is= substr_count($message['url'], 'https');
        if($is==0){
            $message['url']= str_replace("http","https",$message['url']);
        }
        return self::sendNews($config, $message,$toUser);
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
    public static function sendNewsNews($message, $toUser )
    {
        $config = config('news');
        $is= substr_count($message['url'], 'https');
        if($is==0){
            $message['url']= str_replace("http","https",$message['url']);
        }
        return self::sendNews($config, $message,$toUser);
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
    public static function sendPersonalMessage($message, $toUser)
    {
        $config = config('personal');

        $is= substr_count($message['url'], 'https');
        if($is==0){
            $message['url']=  str_replace("http","https",$message['url']);
        }
        return self::sendTextCard($config, $message,$toUser);
    }


    /**
     * 园区活动发送文本卡片
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
    public static function sendActivityMessage($message, $toUser)
    {
        $config = config('activity');

        $is= substr_count($message['url'], 'https');
        if($is==0){
            $message['url']=  str_replace("http","https",$message['url']);
        }
        return self::sendTextCard($config, $message,$toUser);
    }


    //查找园区id
    public function findParkid($Department)
    {
        if ($Department == 1) {
            return 1;
        }
        $WeDepartment = new WechatDepartment();
        $de = $WeDepartment->where('id', $Department)->find();
        if ($de['parentid'] == 1) {
            return $de['id'];
        } else {
            return $this->findParkid($de['parentid']);
        }
    }

    static function sendNews2($config, $message, $toUser="@all")
    {
        $weObj = new TPWechat($config);
        $data = [
            'touser' => 15706844655,
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


    /**推个人中心，推送人员选择公共方法
     *$type =1  该园区运营人员
     *$type =2  该园区物业管理
     *$type =3  该园区运营人员+物业管理
     *$type =4  该用户
     *
     * $message=[
     *   "title" => "物业保修提示",
     *   "description" => date('m月d', $data['create_time']) . "\n服务类型：" . $data['type_text'] . "\n服务地点：" . $data['address'] . "\n联系人员：" . $data['name'] . "\n联系电话：" . $data['mobile'],
     *   "url" => '']
     *
     *retur  true/false
     */
    public function commonSend($type, $message, $userid = "", $appid = 0, $park_id)
    {
        $wechatUser = new WechatUser();
        $useridlist = "";

        //该园区运营的department——id
        switch ($park_id) {
            case  3 :
                $department_id = 76;
                $propertyDepartment = 86;
                break;
            case  80 :
                $department_id = 88;
                $propertyDepartment = 90;
                break;
            default:
                $department_id = 76;
                $propertyDepartment = 86;
                break;
        }
        switch ($type) {
            //运营
            case 1 :
                $user = $wechatUser->where(['department' => $department_id, 'status' => 1])->select();
                foreach ($user as $value) {
                    if (isset($value->operational->appids)) {
                        $appids = empty($value->operational->appids) ? array() : json_decode($value->operational->appids);
                        if (in_array($appid, $appids)) {
                            $useridlist .= '|' . $value['userid'];
                        }
                    }
                }
                break;
            //物业
            case 2 :
                $user = $wechatUser->where(['department' => $propertyDepartment, 'park_id' => $park_id])->select();
                foreach ($user as $value2) {
                    $useridlist .= '|' . $value2['userid'];
                }
                break;
            case 3:
                //该园区运营团队
                $user1 = $wechatUser->where(['department' => $department_id, 'status' => 1])->select();
                foreach ($user1 as $value) {
                    if (isset($value->operational->appids)) {
                        $appids = empty($value->operational->appids) ? array() : json_decode($value->operational->appids);
                        if (in_array($appid, $appids)) {
                            $useridlist .= '|' . $value['userid'];
                        }
                    }
                }
                //该园区物业管理
                $user2 = $wechatUser->where(['department' => $propertyDepartment, 'park_id' => $park_id, 'status' => 1])->select();
                foreach ($user2 as $value2) {
                    $useridlist .= '|' . $value2['userid'];
                }
                break;

            case 4:
                $useridlist = $userid;
                break;
        }
        if (!empty($useridlist)) {
            $res = $this->sendPersonalMessage($message, $useridlist);
        } else {
            return true;
        }
        if ($res['errcode'] == 0) {
            return true;
        } else {

            return false;
        }
    }



}