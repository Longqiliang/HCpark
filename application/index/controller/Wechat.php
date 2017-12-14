<?php
/**
 * Created by PhpStorm.
 * User: 虚空之翼
 * Date: 2017/4/9
 * Time: 下午5:27
 */

namespace app\index\controller;

use app\common\behavior\Service;
use app\common\model\FeePayment;
use app\index\model\WechatUser;
use app\index\model\WechatDepartment;
use think\Loader;
use think\Log;
use wechat\TPWechat;
use think\Controller;
use app\common\model\ThirdUser as ThirdUserModel;
use app\common\model\PayLog as PayLogModel;
use app\common\model\PointsLog as PointsLogModel;
use app\index\model\PropertyServer;
use app\index\model\WaterService;
use app\index\model\ActivityComment;
use  app\index\model\Activity;


class Wechat extends Controller
{
    public function index()
    {
        phpinfo();
    }

    public function callback()
    {
        $weObj = new TPWechat(config('wechat'));
        $weObj->valid();
    }

    public function valid()
    {
        $weObj = new TPWechat(config('scan'));
        $weObj->valid();
    }

    //监听第一次进入园区简介的监听
    public function listener()
    {
        $user = new WechatUser();
        Loader::import('wechat\TPWechat', EXTEND_PATH);
        $data = [
            'appid' => 'ww68db00a56b949cff',
            'token' => '5nKAPmLKP8mJy6VIy',
            'encodingaeskey' => 'fSH5sENzHzaeoegXgYIvJ7KDER4hO4z6PX5lZ8yQDr3',
            'appsecret' => 'nUreq8Yaj3368JBzTUmuZM57kkOUpYJGfN7MPBD8Kg8',
            'agentid' => 1000018
        ];
        $weObj = new TPWechat($data);
        $weObj->valid();//明文或兼容模式可以在接口验证通过后注释此句，但加密模式一定不能注释，否则会验证失败

        $is = "no";;
        $type = $weObj->getRev()->getRevEvent();
        //$weObj->news($new1)->reply();
        //$weObj->getRevFrom();
        //Log::record('log:'.json_encode($weObj->getRev()->getRevEvent()));
        if ($type['event'] == TPWechat::EVENT_ENTER_AGENT) {
            //Log::record('log11:'.json_encode($weObj->getRev()));
            $user_id = $weObj->getRev()->getRevFrom();
            $userInfo = $user->where('userid', $user_id)->find();
            if ($userInfo['is_first'] == 0) {
                $is = "yes";
                $userInfo['is_first'] = 1;
                $userInfo->save();
            }
            $news = array();
            //智新泽地科技发展有限公司简介
            $new0 = [
                'Title' => '浙江智新泽地科技发展有限公司简介',
                'Description' => '浙江智新泽地科技发展有限公司（下简称“智新泽地”）是一家专业的科技园区运营商和创业创新服务提供商。公司以“营造创业创新环境、服务创业创新发展、培育万家企业成长”为宗旨，致力于为创业者提供一流的创业空间、完善的创业创新服务、快速成长的发展环境。',
                'PicUrl' => 'http://xk.0519ztnet.com/index/images/parkprofile/park-img0.png',
                'Url' => 'http://xk.0519ztnet.com/index/Parkprofile/mainPark'
            ];

            //希垦园区简介
            $new1 = [
                'Title' => '希垦科技园区简介',
                'Description' => '希垦科技园”总建筑面积为3.5万平方米，位于余杭区未来科技城核心区域，于2014年11月正式入驻，在政府大力的政策扶持下，园区将重点引进互联网、电子商务、科技型的企业，以培育高新技术企业和互联网服务平台研发为主要目标，目前去化率已完成94%，形成电子商务企业集聚、初创企业快速成长的良好局面。',
                'PicUrl' => 'http://xk.0519ztnet.com/index/images/parkprofile/park-img2.png',
                'Url' => 'http://xk.0519ztnet.com/index/Parkprofile/index/park_id/3'
            ];
            //人工智能产业园简介
            $new2 = [
                'Title' => '人工智能产业园简介',
                'Description' => '人工智能产业园，总用地面积约2.2万平方米，建筑面积近8万平方米。分为A、B、C、D四幢主体合围建筑。本项目将以“营造人工智能产业发展环境，服务人工智能企业创业成长，助推区域经济新发展”为宗旨，深入研究以人工智能为代表的智慧产业发展趋势，围绕人工智能产业发展五大细分产业链，打造人工智能专业孵化器、加速器与倍增器！',
                'PicUrl' => 'http://xk.0519ztnet.com/index/images/parkprofile/park-img3.png',
                'Url' => 'http://xk.0519ztnet.com/index/Parkprofile/index/park_id/80'
            ];
            //互联网产业大厦简介
            $new3 = [
                'Title' => '互联网产业大厦简介',
                'Description' => '“浙江互联网产业大厦”位于钱江金融城区，西靠凤起路延伸段及运河东路，南临杭海路，遥望钱塘江景。是智新泽地根据江干区科技产业发展总体规划，与江干区政府在城东钱江金融城区域共同打造的科技平台项目，也是智新泽地在江干区落地发展的第一个科技产业园项目。',
                'PicUrl' => 'http://xk.0519ztnet.com/index/images/parkprofile/park-img1.png',
                'Url' => 'http://xk.0519ztnet.com/index/Parkprofile/index/park_id/81'
            ];
            array_push($news, $new0);
            array_push($news, $new1);
            array_push($news, $new2);
            array_push($news, $new3);
            if ($is == "yes") {
                $weObj->news($news)->reply();
            }
        } elseif ($type['event'] == TPWechat::EVENT_SUBSCRIBE) {
            $weObj->text("欢迎")->reply();
        }
    }

    // 自动登入
    public function login()
    {
        Loader::import('wechat\TPWechat', EXTEND_PATH);
        $weObj = new TPWechat(config('company'));
        $userId = $weObj->getUserId(input('code'), config('company.agentid'));
        //var_dump($userId);
        //var_dump('errcode:'.$weObj->errCode.',msg:'.$weObj->errMsg);
        $userInfo = $weObj->getUserInfo($userId['UserId']);
        $park_id = $this->findParkid($userInfo['department'][0]);
        $data = [
            'userid' => $userInfo['userid'],
            'name' => $userInfo['name'],
            'mobile' => $userInfo['mobile'],
            'gender' => $userInfo['gender'],
            'avatar' => $userInfo['avatar'],
            'department' => $userInfo['department'][0], //只选第一个所属部门
            'park_id' => $park_id
        ];

        $wechatUser = new WechatUser();
        if ($wechatUser->checkUserExist($userInfo['userid'])) {
            $wechatUser->save($data, ['userid' => $userInfo['userid']]);
        } else {
            $wechatUser->save($data);
        }

        session('userId', $userInfo['userid']);
        session('name', $userInfo['name']);
        session('gender', $userInfo['gender']);
        session('avatar', $userInfo['avatar']);
        session('park_id', $park_id);

        // 默认跳转到前一页
        $this->redirect(session('requestUri'));
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

    /**
     * 微信支付返回
     */
    public function notify()
    {

        //接受成功回调支付信息
        $xml = file_get_contents('php://input');
        $arr = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        Log::record($arr);
        $map = [
            'status' => 0,
            'order_id' => $arr['out_trade_no'],
        ];

        //修改订单
        $res = PayLogModel::where($map)->find();
        if ($res) {
            $res->status = 1;
            $res->save();

            //新增购买记录
            $data = [
                'user_id' => $res['user_id'],
                'user_type' => $res['user_type'],
                'type' => 2, //积分购买
                'amount' => $res['points'],
            ];
            PointsLogModel::create($data);

            //修改用户积分
            if ($res['user_type'] == 1) {

                WechatUser::where('userid', $res['user_id'])->setInc('points', $res['points']);
            } else if ($res['user_type'] == 2) {

                ThirdUserModel::where('user_id', $res['user_id'])->setInc('points', $res['points']);
            }

            //返回成功
            $return = ['return_code' => 'SUCCESS', 'return_msg' => 'OK'];
            $xml = '<xml>';
            foreach ($return as $k => $v) {
                $xml .= '<' . $k . '><![CDATA[' . $v . ']]></' . $k . '>';
            }
            $xml .= '</xml>';
            echo $xml;
        }
    }

    // 判断（维修，物业，饮水 ,费用缴纳）推送定时任务
    public function quartz()
    {
   /*     $property = new   PropertyServer();
        $water = new WaterService();
        $time = time() - 900;
        //15 分钟后，状态仍为进行中 ，未推给运营的(is_banner=0) 记录要进行推送
        $propertyBanner = $property->where(['create_time' => array('lt', $time), 'is_banner' => 0, 'status' => 0])->select();
        $waterBanner = $water->where(['create_time' => array('lt', $time), 'is_banner' => 0, 'status' => 0])->select();
        foreach ($propertyBanner as $value) {
            //服务类型 1为空调，2为电梯，3为其他 4 室内保洁
            $is = 1;
            switch ($value['type']) {
                case 1:
                    $type = "空调维修";
                    break;
                case 2:
                    $type = "电梯维修";
                    break;
                case 3:
                    $type = "其他维修";
                    break;
                case 4:
                    $is = 2;
                    break;
            }
            if ($is == 2) {
                $message = [
                    "title" => "保洁服务提示",
                    "description" => "服务地点：" . $value['address'] . "\n服务时间：" . date('m月d日', $value['clear_time']) . "\n联系人员：" . $value['name'] . "\n联系电话：" . $value['mobile'],
                    "url" => 'http://' . $_SERVER['HTTP_HOST'] . '/index/service/historyDetail/appid/4/can_check/yes/id/' . $value['id']
                ];
                $app_id = 4;
            } else {
                $message = [
                    "title" => "物业报修提示",
                    "description" => "服务类型：" . $type . "\n服务地点：" . $value['address'] . "\n联系人员：" . $value['name'] . "\n联系电话：" . $value['mobile'],
                    "url" => 'http://' . $_SERVER['HTTP_HOST'] . '/index/service/historyDetail/appid/2/can_check/yes/id/' . $value['id']
                ];
                $app_id = 3;
            }
            //推送给运营
            $reult = $this->commonSend(1, $message, '', $app_id);
            if ($reult) {
                $value['is_banner'] = 1;
                $value->save();
            }
        }
        foreach ($waterBanner as $value) {
            $message = [
                "title" => "饮水服务提示",
                "description" => "送水地点：" . $value['address'] . "\n送水桶数：" . $value['number'] . "\n联系人员：" . $value['name'] . "\n联系电话：" . $value['mobile'],
                "url" => 'http://' . $_SERVER['HTTP_HOST'] . '/index/service/historyDetail/appid/3/can_check/yes/id/' . $value['id']
            ];

            //推送给运营
            $reult = $this->commonSend(1, $message, '', 3);
            if ($reult) {
                $value['is_banner'] = 1;
                $value->save();
            }
        }*/
        //费用缴纳3天内未点击重复推送
        $userId = '';
        $feepayment = new FeePayment();
        $times = time() - 60 * 60 * 24 * 3;
        $list = $feepayment->distinct(true)->field('type,company_id')->select();
        foreach ($list as $k => $v) {
            $maps[$k] = ['type' => $v['type'], 'company_id' => $v['company_id']];
        }
        foreach ($maps as $k => $v) {
            $map = $v;
            $info = $feepayment->where($map)->order('id desc')->find();

            if (strtotime($info['create_time']) < $times && $info['onclick'] != 1 && $info['is_banner']==0) {
                $type = [1 => "水电费", 2 => "物业费", 3 => "房租费", 4 => "公耗费"];
                $userList = WechatUser::where(['department' => $info['company_id'], 'fee_status' => 1])->select();
                if ($info['type'] == 4) {
                    $info['type'] = 2;
                }
                $title = $type[$info['type']];
                $message = [
                    "title" => $title . "缴纳提示",
                    "description" => "您的" . $title . "（到期时间：" . $info['expiration_time'] . "）应缴纳" . $info['fee'] . "元",
                    "url" => 'http://' . $_SERVER['HTTP_HOST'] . '/index/service/feedetail/t/' . $info['type'] . '/id/1',
                ];
                $message2=[
                    "title" => $title . "缴纳提示",
                    "description" => $info['name'].$title."（到期时间：" . $info['expiration_time'] . "）应缴纳" . $info['fee'] . "元,仍未缴纳",
                    "url" => 'http://' . $_SERVER['HTTP_HOST'] . '/index/service/historyDetail/appid/1/can_check/yes/id/' . $info['id']
                ];
                foreach ($userList as $k => $v) {
                    $userId .= '|'.$v;
                }
                if ($userId != ''){
                    $reult = $this->commonSend(4, $message, $userId);
                    $reult2 = $this->commonSend(1, $message2, '',1);
                    $info['is_banner']=1;
                    $info->save();
                }

            }
        }

        //activity
        //活动前一天给用户推送
        $time = time()+60 * 60 * 24;
        $activityList =Activity::where(['start_time'=>['lt',$time],'status'=>2])->select();
        foreach ($activityList as $value){
            $userList = isset($value->user)?$value->user:array();
            foreach ($userList as $user){
               if($user['status']==1&&$user['is_send']==0) {
                   $message = [
                       "title" => "活动报名提示",
                       "description" => " 您报名参加的" . $value['name'] . "于" . date('Y.m.d', $value['start_time']) . "（明天）即将开始，请按照活动要求准时参加，点击查看活动详情。",
                       "url" => 'https://' . $_SERVER['HTTP_HOST'] . '/index/activity/detail/id/' . $value['id'],
                   ];
                   $reult = $this->commonSend(4, $message, $user['userid']);

                   ActivityComment::where('id',$user['id'])->update(['is_send'=>1]);

               }

            }
        }
    }





    public function commonSend($type, $message, $userid = "", $appid = 0)
    {
        $service =new service();
        $wechatUser = new WechatUser();
        $useridlist = "";
        $park_id = 3;
        //该园区运营的department——id
        switch ($park_id) {
            case  3 :
                $department_id = 76;
                break;
            default:
                $department_id = 76;
                break;
        }
        switch ($type) {
            //运营
            case 1 :
                $user = $wechatUser->where(['department'=>$department_id,'status'=>1])->select();
                foreach ($user as $value) {
                    if (isset($value->operational->appids)) {
                        $appids = empty($value->operational->appids)?array():json_decode($value->operational->appids);
                        if (in_array($appid, $appids)) {
                            $useridlist .= '|' . $value['userid'];
                        }
                    }
                }

                break;
            //物业
            case 2 :
                $user = $wechatUser->where(['tagid' => 2, 'park_id' => $park_id])->select();
                foreach ($user as $value2) {
                    $useridlist .= '|' . $value2['userid'];
                }
                break;
            /*
             * 现在没有运营+物业一起推的情况
             * case 3:
                //该园区运营团队
                $user1 = $wechatUser->where('department', $department_id)->select();
                foreach ($user1 as $value) {
                    $useridlist .= '|' . $value['userid'];
                }
                //该园区物业管理
                $user2 = $wechatUser->where(['tagid' => 2, 'park_id' => $park_id])->select();
                foreach ($user2 as $value2) {
                    $useridlist .= '|' . $value2['userid'];
                }
                break;*/

            case 4:
                $useridlist = $userid;
                break;

        }
        if (!empty($useridlist)) {
            $res = $service->sendPersonalMessage($message, $useridlist);
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