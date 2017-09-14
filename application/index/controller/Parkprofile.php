<?php
/**
 * Created by PhpStorm.
 * User: shen
 * Date: 2017/9/2
 * Time: 下午3:53
 */

namespace app\index\controller;

use app\index\model\WechatUser;
use think\Controller;
use app\index\model\Park;
use wechat\TPWechat;
use think\Loader;

class Parkprofile extends Base
{
    /*园区简介，前台加判断，如果没有全景图片，就不显示*/
    public function index()
    {
        //取线上url的park_id，先写死；
        $parkid = input('park_id');
        $map = array(
            'id' => $parkid,
        );

        $info = Park::where($map)->field('panorama_cover,panorama_link,content')->find();

        $this->assign('info', $info);
        return $this->fetch();
    }

    public function mainPark()
    {

        return $this->fetch();
    }

    //监听第一次进入园区简介的监听
    public function listener()
    {
        $user = new WechatUser();
        Loader::import('wechat\TPWechat', EXTEND_PATH);
        $weObj = new TPWechat(config('parkinfo'));
        $check =$weObj->valid(true);//明文或兼容模式可以在接口验证通过后注释此句，但加密模式一定不能注释，否则会验证失败
        $is=false;
        return $check['msg'];
        $type = $weObj->getRev()->getRevType();
        if($type==TPWechat::EVENT_ENTER_AGENT) {

            $user_id = $weObj->getRev()->getRevFrom();
            $userInfo = $user->where('userid', $user_id)->find();

            if ($userInfo['is_first'] == 0) {
                $is = true;
                $userInfo['is_first'] = 1;
                $userInfo->save();
            }

            $news = array();
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
            array_push($news, $new1);
            array_push($news, $new2);
            array_push($news, $new3);
            if($is&&$user_id=='15706844655'){
                $weObj->news($news)->reply();
            }
        }

        /*switch ($type) {
            case TPWechat::MSGTYPE_TEXT:
                $weObj->text("hello, I'm wechat")->reply();
                exit;
                break;
            case TPWechat::MSGTYPE_EVENT:
                break;
            //事件类型为用户进入应用
            case TPWechat::EVENT_ENTER_AGENT:


                $weObj->news($news)->reply();

                exit;
                break;
            case TPWechat::
            default:
                $weObj->text("help info")->reply();
        }*/
        // $result1 = $weObj->sendMessage($data);


    }


}