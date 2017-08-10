<?php
/**
 * 反馈
 * User: zyf
 * Date: 2017/7/12
 * Time: 下午3:01
 */

namespace app\index\controller;

use wechat\TPWechat;
use app\common\model\WechatDepartment;
use app\index\model\Feedback as FeedbackModel;
use app\index\model\WechatUser;
use think\Loader;
class Feedback extends Base
{
    //反馈记录(用户)
    public function index() {
        $map = [
            'create_user' => session('userId'),
            'park_id'=>session('user_auth')['park_id'],
        ];
        $list = FeedbackModel::where($map)->order('status')->select();
        $this->assign('list',$list);

        return $this->fetch();
    }
    //意见反馈（用户）
    public function demand() {
        //提交反馈
        if(IS_POST){
            $data = [
                'title' => input('title'),
                'create_user' => session('userId'),
                'content' => input('content'),
                'park_id'=>session('user_auth')['park_id'],
            ];
            $result = FeedbackModel::create($data);
            if($result){

                //todo 推送给标签为园区领导（文字卡片推送）
                $map=[
                    'status'=>0,
                    'create_user'=>session('userId'),
                    'title' => input('title'),
                    'content' => input('content'),
                ];
                $list = FeedbackModel::where($map)->find();
                Loader::import('wechat\TPWechat', EXTEND_PATH);
                $weObj = new TPWechat(config('reply'));
                $data = [
                    "touser" => @all,
                    'safe' => 0,
                    'msgtype' => 'textcard',
                    'agentid' => 1000007,
                    'textcard' => [
                        'title' => "您有一条最新信息",
                        'description' => $list['create_time']."</br>"."海创大厦："."</br>尊敬的领导，我们公司现在发展有新的需求......，希望园区部门给出解答",
                        'url' =>config('web_url').'/index/feedback/detail/id/'.$list['id'],
                    ]
                ];
                $result1 = $weObj->sendMessage($data);
                //var_dump($result1);
                //var_dump($weObj->errCode.'|'.$weObj->errMsg);
                if ($result1['errcode'] == 0 ) {
                    return  $this->success('提交成功','index',$result);
                } else {
                    return    $this->error('推送失败',$result);
                }
            }else{
                return   $this->error('提交失败',$result);
            }
        }else{
            return $this->fetch();
        }
    }

    //反馈详情
    public function  detail() {
        $map = [
            'id' =>input('id'),
            'park_id'=>session('user_auth')['park_id'],
        ];
        $list = FeedbackModel::where($map)->find();
        $this->assign('info',$list);
        return $this->fetch();
    }

    //意见回复首页（园区领导）
    public function  replylist() {
        $map = [
            'status' =>0,
            'park_id'=>session('user_auth')['park_id'],
        ];
        $map2 = [
            'status' =>1,
            'park_id'=>session('user_auth')['park_id'],
        ];
        $noReply = FeedbackModel::where($map)->select();
        $this->assign('noReply', $noReply);
        $replied = FeedbackModel::where($map2)->order('id desc')->select();
        $this->assign('replied', $replied);
        return $this->fetch();
    }
    //回复
    public  function  reply() {
        //点击回复后
        if (IS_POST) {
            $data = [
                'reply' => input('reply'),
                'reply_user' => session('userId'),
                'reply_time'=> time(),
                'status'=>1,
                'park_id'=>session('user_auth')['park_id'],
            ];
            $result = FeedbackModel::where('id', input('id'))->update($data);
            if($result){
                //todo 推送给该反馈对应用户一条文字卡片推文（已回复）
                $list = FeedbackModel::where('id',input('id'))->find();
                Loader::import('wechat\TPWechat', EXTEND_PATH);
                /*$weObj = new TPWechat(config('feedback'));*/
                $u_d=WechatUser::where('userid',$list['create_user'])->field('department')->find();
                $depart=WechatDepartment::where('id',$u_d['department'])->find();
                $data = [
                    'touser'=>$list['create_user'],
                    'safe' => 0,
                    'msgtype' => 'textcard',
                    'agentid' => 1000006,
                    'textcard' => [
                        'title' => "您有一条最新信息",
                        'description' => date('Y-m-d H:i:s',$list['reply_time'])."</br>".$depart['name']."</br>根据你所提供的意见，我领导已经回复，批示内容如下，如有任何问题，欢迎咨询。O(∩_∩)O谢谢！",
                        'url' =>config('web_url').'/index/feedback/detail/id/'.input('id'),
                    ]
                ];
                $weObj = new TPWechat(config('feedback'));
                $msg = $this->send($data,$weObj);

                //var_dump($weObj->errCode.'|'.$weObj->errMsg);
                if ($msg['errcode'] == 0 ) {

                    return  $this->success('回复成功','replylist',$msg);
                } else {
                    return   $this->error('推送失败',$msg);
                }

            }else{
                return  $this->error('回复失败',$result);
            }
        }
        //点击回复前
        else {
            $feedback = FeedbackModel::get(input('id'));
            $this->assign('feedback', $feedback);
            return $this->fetch();
        }
    }


    /*发送给用户已回复推送*/
    public function send($data,$weObj) {
        Loader::import('wechat\TPWechat', EXTEND_PATH);
        $result = $weObj->sendMessage($data);
        return $result;
    }




}










