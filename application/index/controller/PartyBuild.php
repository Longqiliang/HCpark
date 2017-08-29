<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/8/28
 * Time: 下午3:24
 */
namespace app\index\controller;
use  app\common\model\PartyBuilding as PartyBuildingModel;
use app\common\model\PartyComment;

class PartyBuild extends Base{

    public function index(){
        $list1=PartyBuildingModel::where(['type'=>1,'status'=>1])->order('id desc')->find();
        $list2=PartyBuildingModel::where(['type'=>2,'status'=>1])->order('id desc')->find();
        $list3=PartyBuildingModel::where(['type'=>3,'status'=>1])->order('id desc')->find();
        $this->assign('list1',$list1);
        $this->assign('list2',$list2);
        $this->assign('list3',$list3);
        dump($list1);
        //return $this->fetch();
    }

    public function showList(){
        $type= input('type');
        switch ($type){
            case 1:
                $listMap = [
                    'type' => 1,
                    'status' => 1,
                    'park_id'  =>session("park_id")
                ];
                $list = PartyBuildingModel::where($listMap)->order('create_time desc')->limit(6)->select();

                break;
            case 2:
                $listMap = [
                    'type' => 2,
                    'status' => 1,
                    'park_id'  =>session("park_id")
                ];
                $list = PartyBuildingModel::where($listMap)->order('create_time desc')->limit(6)->select();

                break;
            case 3:
                $listMap = [
                    'type' => 3,
                    'status' => 1,
                    'park_id'  =>session("park_id")
                ];
                $list = PartyBuildingModel::where($listMap)->order('create_time desc')->limit(6)->select();

                break;
            default :

                break;
        }

        return dump($list);
        $this->assign("list",$list);

        return $this->fetch();

    }
        /*新闻详细页面*/
        public function detail(){
            $news = PartyBuildingModel::get(input('id'));
            $this->assign('news', $news);
            // 评论列表
            $map = [
                'target_id' => input('id')
            ];
            $comments = PartyComment::where($map)->order("create_time")->limit(6)->select();
            foreach( $comments as $v){
                $v['header']=isset($v->wechatuser->header)?$v->wechatuser->header:"";
            }
            foreach ($comments as $value){
            }
            $this->assign('comments', json_encode($comments));

            // 添加阅读量
            PartyBuildingModel::where('id', input('id'))->setInc('views');

            return $this->fetch();
        }
        /*获取更多评论*/
        public function getMore(){
            $len = input("length");
            $newsId = input("news_id");
            $comments = PartyComment::where(['target_id' => $newsId])
                ->order("create_time")
                ->limit($len, 6)
                ->select();
            if ($comments) {
                return json(['code' => 1, 'data' => $comments]);
            } else {
                return json(['code' => 0, 'msg' => "没有更多内容了"]);
            }


        }

        /*获取更多新闻*/
        public function getMoreList (){
            $type = input("type");
            $len =input("length");
            if ($type ==1 ){
                $listMap = [
                    'type' => 1,
                    'status' => 1,
                    'park_id'  =>session("park_id")
                ];
                $list = PartyBuildingModel::where($listMap)->order('create_time desc')->limit($len,6)->select();
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
                $list = PartyBuildingModel::where($listFileMap)->order('create_time desc')->limit($len,6)->select();
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
                $list =  PartyBuildingModel::where($articleMap)->order('create_time desc')->limit($len,6)->select();
                if ($list){

                    return json(['code'=>3,'data'=>$list]);
                }else{

                    return json(['code'=>0,'data'=>"没有更多了"]);
                }
            }

        }










}