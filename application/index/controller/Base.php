<?php
/**
 * Created by PhpStorm.
 * User: 虚空之翼
 * Date: 2017/5/8
 * Time: 下午2:53
 */

namespace app\index\controller;


use app\common\model\PartyComment;
use app\common\model\UnionComment;
use think\Controller;
use think\Loader;
use wechat\TPWechat;
use app\index\model\WechatUser ;
use app\admin\model\Config as ConfigModel;
use app\index\model\Collect;
use app\index\model\Comment;

class Base extends Controller
{
    protected function _initialize(){
        session('userId', '15706844655');//测试
        session('park_id', 3);//测试
//        session('thirdUserId', '1001');

        /* 读取数据库中的配置 */
        $config = cache('db_config_data');
        if(!$config) {
            $configModel = new ConfigModel();
            $config = $configModel->lists();
            cache('db_config_data',$config);
        }
        config($config); //添加配置

        session('requestUri', 'http://'.$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"]);
        $userId = session('userId');

        Loader::import('wechat\TPWechat', EXTEND_PATH);
        $weObj = new TPWechat(config('company'));
        if(empty($userId)) {
            $redirect_uri = config("login_url");
            $url = $weObj->getOauthRedirect($redirect_uri);
            $this->redirect($url);
        }

        // 2获取jsapi_ticket
//        $jsApiTicket = cache('jsapiticket');
//        if(empty($jsApiTicket) || $jsApiTicket=='') {
//            cache('jsapiticket', $weObj->getJsTicket(), 7000); // 官方7200,设置7000防止误差
//        }
    }

    /**
     * 获取企业号签名
     */
    public function jssdk(){
        Loader::import('wechat\TPWechat', EXTEND_PATH);
        $weObj = new TPWechat(config('wechat'));
        $url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $jsSign = $weObj->getJsSign($url);
        $this->assign("jsSign", $jsSign);
    }
    // 添加评论
    public function addComment() {
        $data = [
            'target_id' => input('targetId'),
            'user_id' => session('userId'),
            'content' => input('content'),
        ];
        $userinfo = WechatUser::where('userid',session('userId'))->field('name,header,avatar')->find();
        $data['user_name'] = $userinfo['name'];
        $result = Comment::create($data);
        if(!empty($userinfo['header'])){

            $result['header']=$userinfo['header'];

        }else{
            $result['header']=!empty($userinfo['avatar'])?$userinfo['avatar']:"";

        }
        if($result) {

            return $this->success('评论成功', '', $result);
        } else {

            return $this->error('评论失败');
        }
    }
    //党建添加评论
    public function addComments(){
        $data = [
            'target_id' => input('id'),
            'user_id' => session('userId'),
            'content' => input('content'),
        ];
        $userinfo = WechatUser::where('userid',session('userId'))->field('name,header,avatar')->find();
        $data['user_name'] = $userinfo['name'];
        $result = PartyComment::create($data);
        if(!empty($userinfo['header'])){

            $result['header']=$userinfo['header'];

        }else{
            $result['header']=!empty($userinfo['avatar'])?$userinfo['avatar']:"";

        }
        if($result) {

            return $this->success('评论成功', '', $result);
        } else {

            return $this->error('评论失败');
        }
    }

    //工会联盟添加评论
    public function unionComments(){
        $data = [
            'target_id' => input('targetId'),
            'user_id' => session('userId'),
            'content' => input('content'),
        ];
        $userinfo = WechatUser::where('userid',session('userId'))->field('name,header,avatar')->find();
        $data['user_name'] = $userinfo['name'];
        $result = UnionComment::create($data);
        if(!empty($userinfo['header'])){

            $result['header']=$userinfo['header'];

        }else{
            $result['header']=!empty($userinfo['avatar'])?$userinfo['avatar']:"";

        }
        if($result) {

            return $this->success('评论成功', '', $result);
        } else {

            return $this->error('评论失败');
        }
    }


    // 收藏
    public function addCollect() {
        $data = [
            'target_id' => input('targetId'),
            'user_id' => session('userId'),
            'type' => input('type'),
        ];

        $result = Collect::create($data);
        if($result) {
            return $this->success('收藏成功', '', $result);
        } else {
            return $this->error('收藏失败');
        }
    }
    //取消收藏
    public  function delCollect() {
        $data = [
            'target_id' => input('targetId'),
            'user_id' => session('userId'),
            'type' => input('type'),
        ];
        $result = Collect::where($data)->delete();
        if($result) {
            return  $this->success('取消收藏成功', '', $result);
        } else {
            return $this->error('取消收藏失败');
        }
    }
    //评论分页
    public function moreComment() {
        $lastId = input('lastId', 0);
        $map = [
            'target_id' => input('targetId')
        ];
        if ($lastId != 0) {  $map['id'] = ['<', $lastId]; }
        $comments = Comment::where($map)->order('id desc')->limit(6)->select();
        foreach ($comments as $value){
            $userinfo['header']=isset($value->wechatuser->header)?$value->wechatuser->header:"";
            $userinfo['avatar']=isset($value->wechatuser->avatar)?$value->wechatuser->avatar:"";
            if(!empty($userinfo['header'])){

                $value['header']=$userinfo['header'];

            }else{
                $value['header']=!empty($userinfo['avatar'])?$userinfo['avatar']:"";

            }
        }
        return json(['total'=>count($comments), 'comments'=>$comments]);
    }

    //工会联盟评论分页
    public function moreUnion() {
        $lastId = input('lastId', 0);
        $map = [
            'target_id' => input('targetId')
        ];
        if ($lastId != 0) {  $map['id'] = ['<', $lastId]; }
        $comments = UnionComment::where($map)->order('id desc')->limit(6)->select();
        foreach ($comments as $value){
            $userinfo['header']=isset($value->wechatuser->header)?$value->wechatuser->header:"";
            $userinfo['avatar']=isset($value->wechatuser->avatar)?$value->wechatuser->avatar:"";
            if(!empty($userinfo['header'])){

                $value['header']=$userinfo['header'];

            }else{
                $value['header']=!empty($userinfo['avatar'])?$userinfo['avatar']:"";

            }
        }
        return json(['total'=>count($comments), 'comments'=>$comments]);
    }


}