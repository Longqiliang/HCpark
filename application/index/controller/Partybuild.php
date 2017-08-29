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
use app\index\model\WechatUser;

class Partybuild extends Base{

    public function index(){
        $list1=PartyBuildingModel::where(['type'=>1,'status'=>1])->order('id desc')->find();
        $list2=PartyBuildingModel::where(['type'=>2,'status'=>1])->order('id desc')->find();
        $list3=PartyBuildingModel::where(['type'=>3,'status'=>1])->order('id desc')->find();
        $this->assign('list1',$list1);
        $this->assign('list2',$list2);
        $this->assign('list3',$list3);

        return $this->fetch();
    }
        /*党建新闻列表*/
    public function showList(){
        $type= input('type');
        $list=[];
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


        $this->assign("list",json_encode($list));
        $this->assign('type',$type);

        return $this->fetch();

    }
        /*新闻详细页面*/
    public function detail(){
        $id= input('id');
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
        $this->assign('id',$id);
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

        /*党员统计*/
    public function countParty(){
        $parkId = session("park_id");
        $data[0]= WechatUser::where(['age'=>['<',20]])->count();
        $data[1]= WechatUser::where(['age'=>['between',[20,30]]])->count();
        $data[2]= WechatUser::where(['age'=>['between',[30,40]]])->count();
        $data[3]= WechatUser::where(['age'=>['between',[40,50]]])->count();
        $data[4]= WechatUser::where(['age'=>['>',50]])->count();
        $this->assign('data',$data);

        return $this->fetch();

    }








}