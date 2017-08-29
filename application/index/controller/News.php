<?php
/**
 * Created by PhpStorm.
 * User: 虚空之翼
 * Date: 2017/5/8
 * Time: 下午2:55
 */

namespace app\index\controller;

use app\index\model\Collect;
use app\index\model\Comment;
use app\index\model\News as NewsModel;

class News extends Base
{
    // 新闻动态列表
    public function index() {
        // 轮播图
        $bannerMap = [
            'status' => 1,
            'type' => 1,
            'is_banner' => 1,
            'park_id'  =>session("park_id")
        ];
        $count = NewsModel::where($bannerMap)->order('create_time desc')->count();
        $banners = NewsModel::where($bannerMap)->order('create_time desc')->limit(3)->select();



        // 新闻列表
        $listMap = [
            'type' => 1,
            'status' => 1,
            'is_banner' => 0,
            'park_id'  =>session("park_id")
        ];
        $list = NewsModel::where($listMap)->order('create_time desc')->limit(6)->select();


        if($count==3){
            //如果有3张轮播图，优先使用轮播图
            $this->assign('banners',$banners);
            $this->assign('list',$list);
        }elseif($count==2){
            //2张轮播图
            $res= NewsModel::where($bannerMap)->order('create_time desc')->limit(2)->select();
            $banners[0]=$res[0];
            $banners[1]=$res[1];
            $banners[2] = NewsModel::where($listMap)->order('create_time desc')->find();
            $this->assign('banners',$banners);
            $list = NewsModel::where($listMap)->order('create_time desc')->limit(1,6)->select();
            $this->assign('list',$list);
        }elseif($count==1){
            //1张轮播图
            $banners[0] = NewsModel::where($bannerMap)->order('create_time desc')->find();
            $res = NewsModel::where($listMap)->order('create_time desc')->limit(2)->select();
            $banners[1]=$res[0];
            $banners[2]=$res[1];
            $this->assign('banners',$banners);
            $list = NewsModel::where($listMap)->order('create_time desc')->limit(2,6)->select();
            $this->assign('list',$list);
        }elseif($count==0){
            //没有轮播图就选择列表最新的3张
            $list = NewsModel::where($listMap)->order('create_time desc')->limit(3,6)->select();
            $banners = NewsModel::where($listMap)->order('create_time desc')->limit(3)->select();
            $this->assign('banners',$banners);
            $this->assign('list',$list);
        }

        return $this->fetch();
    }

    // 新闻动态详情
    public function detail() {
        $news = NewsModel::get(input('id'));
        $this->assign('news', $news);

        // 评论列表
        $map = [
            'target_id' => input('id')
        ];
        $comments = Comment::where($map)->order('id desc')->limit(6)->select();

        foreach( $comments as $v){
           $header =isset($v->wechatuser->header)?$v->wechatuser->header:"";
            if(!empty($header)){
                $v['header']=$header;
            }else {
                $v['header'] = isset($v->wechatuser->avatar) ? $v->wechatuser->avatar : "";
            }
        }
        foreach ($comments as $value){
        }
        $this->assign('comments', json_encode($comments));
        // 是否已经收藏
        $collectMap = [
            'target_id' => input('id'),
            'user_id' => session('userId')
        ];
        $collect = Collect::where($collectMap)->find();
        //echo json_encode($comments);
        $this->assign('collect', json_encode($collect));

        // 添加阅读量
        NewsModel::where('id', input('id'))->setInc('views');

        return $this->fetch();
    }

    // 园区通告列表
    public function policy() {
        $listFileMap = [
            'type' => 2,
            'status' => 1,
            'park_id'  =>session("park_id")
        ];
        $listFile = NewsModel::where($listFileMap)->order('create_time desc')->limit(6)->select();
        $this->assign('listFile', $listFile);


        return $this->fetch();
    }

    //好文分享
    public function article (){
        $articleMap= [
            'type' => 3,
            'status' => 1,
            'park_id'  =>session("park_id")
        ];
        $articleList =  NewsModel::where($articleMap)->order('create_time desc')->limit(6)->select();
        $this->assign('article',  $articleList);

        return $this->fetch();

    }

    public function getMoreList (){
        $type = input("type");
        $len =input("length");
        if ($type ==1 ){
            $listMap = [
                'type' => 1,
                'status' => 1,
                'is_banner' => 0,
                'park_id'  =>session("park_id")
            ];
            $list = NewsModel::where($listMap)->order('create_time desc')->limit($len,6)->select();
            if ($list){

                return json(['code'=>1,'data'=>$list]);
            }else{

                return json(['code'=>0,'data'=>"没有更多了"]);
            }
        }else if($type ==2){
            $listFileMap = [
                'type' => 2,
                'status' => 1
            ];
            $list = NewsModel::where($listFileMap)->order('create_time desc')->limit($len,6)->select();
            if ($list){

                return json(['code'=>2,'data'=>$list]);
            }else{

                return json(['code'=>0,'data'=>"没有更多了"]);
            }
        }else{
            $articleMap= [
                'type' => 3,
                'status' => 1
            ];
            $list =  NewsModel::where($articleMap)->order('create_time desc')->limit($len,6)->select();
            if ($list){

                return json(['code'=>3,'data'=>$list]);
            }else{

                return json(['code'=>0,'data'=>"没有更多了"]);
            }
        }

    }



}