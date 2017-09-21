<?php
/**
 * Created by PhpStorm.
 * User: 虚空之翼
 * Date: 2017/4/9
 * Time: 下午5:27
 */

namespace app\index\controller;

use app\common\behavior\Service;
use app\index\model\WechatUser;
use app\index\model\WechatDepartment;
use think\Loader;
use think\Log;
use wechat\TPWechat;
use think\Controller;
use app\common\model\ThirdUser as ThirdUserModel;
use app\common\model\PayLog as PayLogModel;
use app\common\model\PointsLog as PointsLogModel;

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

        $is= "no";;
        $type = $weObj->getRev()->getRevEvent();
        //$weObj->news($new1)->reply();
        //$weObj->getRevFrom();
        //Log::record('log:'.json_encode($weObj->getRev()->getRevEvent()));
        if($type['event']==TPWechat::EVENT_ENTER_AGENT) {
            //Log::record('log11:'.json_encode($weObj->getRev()));
            $user_id = $weObj->getRev()->getRevFrom();
            $userInfo = $user->where('userid', $user_id)->find();
            if ($userInfo['is_first'] == 0) {
                $is = "yes";
                $userInfo['is_first'] = 1;
                $userInfo->save();
            }
            $news = array();
            //希垦园区简介
            $new1 =[
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
            array_push($news, $new1);
            array_push($news, $new2);
            array_push($news, $new3);
            if($is=="yes"){
                $weObj->news($news)->reply();
            }
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
            //'department' => $userInfo['department'][0], //只选第一个所属部门
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

    public function  test(){
        Loader::import('wechat\TPWechat', EXTEND_PATH);
        $weObj = new TPWechat(config('personal'));
        $data = [
            "touser" => "testL1an",
            'safe' => 0,
            'msgtype' => 'textcard',
            'agentid' => 1000008,
            'textcard' => [
                'title' => "您有一条最新信息",
                'description' => "希垦科技园：\n尊敬的领导，我们公司现在发展有新的需求......，希望园区部门给出解答",
                'url' =>'http://xk.0519ztnet.com/index/feedback/reply/id/1',
            ]
        ];
        $result1 = $weObj->sendMessage($data);
        Log::record(json_encode($weObj->errCode.'|'.$weObj->errMsg));


    }



}