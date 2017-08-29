<?php
/**
 * Created by PhpStorm.
 * User: 虚空之翼
 * Date: 2017/5/8
 * Time: 下午2:55
 */

namespace app\index\controller;

use app\index\model\Collect;
use app\index\model\News as NewsModel;
use app\index\model\Comment as CommentModel ;
use app\index\model\Collect as CollectModel;
use app\index\model\WechatUser;
use think\Controller;

class Policy extends Base
{
    //政策法规首页
    public function index() {
        $list1 =NewsModel::where(['type'=>5, 'park_id'  =>session("park_id")])->limit(6)->select();
        $list2 =NewsModel::where(['type'=>4, 'park_id'  =>session("park_id")])->limit(6)->select();

        $this->assign('list1',$list1);
        $this->assign('list2',$list2);

       return $this->fetch();
    }

    //政策法规详细页面
    public function detail(){
        $userId = session("userId");
        $newsId = input("id");
        $news = NewsModel::where(['id'=>$newsId])->find();
        $comments = CommentModel::where(['target_id'=>$newsId])->order("create_time desc")->limit(6)->select();
        foreach( $comments as $v){
            $v['header']=isset($v->wechatuser->header)?$v->wechatuser->header:"";
        }
        //是否收藏
        $res =Collect::where(['user_id'=>$userId,'target_id'=>$newsId])->find();
        if ($res){
            $this->assign("collect",1);
        }else {
            $this->assign("collect",0);
        }
        // 添加阅读量
        NewsModel::where('id', $newsId)->setInc('views');

        $this->assign('comments',json_encode($comments));

        $this->assign('news',$news);


        return $this->fetch();

    }
    public function listmore(){
        $len = input("length");
        $type =input("type");
        if ($type==1){
            $list2 =NewsModel::where(['type'=>4])->order("create_time desc")->limit($len,6)->select();
            if ($list2){

                return  json(['code'=>1,'data'=>$list2]);
            }else {

                return  json(['code'=>0,'msg'=>"没有更多内容了"]);
            }

        }else {
            $list1 =NewsModel::where(['type'=>5])->limit($len,6)->select();
            if ($list1){

                return  json(['code'=>1,'data'=>$list1]);
            }else {

                return  json(['code'=>0,'msg'=>"没有更多内容了"]);
            }

        }

    }
    //获取更多政法评论
    public function moreComment(){
        $len = input("length");
        $newsId = input("news_id");
        $comments = CommentModel::where(['target_id' => $newsId])
            ->order("create_time")
            ->limit($len, 6)
            ->select();
        if ($comments) {
            return json(['code' => 1, 'data' => $comments]);
        } else {
            return json(['code' => 0, 'msg' => "没有更多内容了"]);
        }
    }




}