<?php
/**
 * Created by PhpStorm.
 * User: shen
 * Date: 2017/8/28
 * Time: 下午2:51
 */
namespace app\index\controller;
use app\index\model\Union as UnionModel;
use app\index\model\UnionLoabour as UnionLoabourModel;
use app\index\model\Collect;
use app\common\model\UnionComment;
class Union extends Base
{
    public function index(){
        $map=[
            'status' => 1,
        ];
        $list1=UnionModel::where($map)->where('type',1)->order('create_time desc')->limit(2)->field('id,title,front_cover')->select();
        $list2=UnionLoabourModel::where($map)->order('create_time desc')->limit(2)->field('id,title,front_cover')->select();
        $list3=UnionModel::where($map)->where('type',3)->order('create_time desc')->field('id,title,front_cover')->limit(2)->select();

        $this->assign('list1',json_encode($list1));
        $this->assign('list2',json_encode($list2));
        $this->assign('list3',json_encode($list3));
        return $this->fetch();
    }
//通知公告和相关活动写一起，传我type1 type2
    public function unionList(){
        if(input('type')==2){
            $lastId = input('lastId', 0);

            $total = input('total');
            $map['status'] = 1;

            $list = UnionLoabourModel::where($map)->order('create_time desc')->limit($total)->field('id,title,create_time,views')->select();

        }else {
            $lastId = input('lastId', 0);

            $map['type'] = input('type');
            $total = input('total');
            $map['status'] = 1;

            $list = UnionModel::where($map)->order('create_time desc')->limit($total)->field('id,title,create_time,views')->select();
        }

        $this->assign('list',json_encode($list));
        $this->assign('type',json_encode(input('type')));
        return $this->fetch();
    }

    public function pull(){
        if(input('type')==2){
            $lastId = input('lastId');
            $map['id'] = ['<', $lastId];

            $total = input('total');
            $map['status'] = 1;

            $list = UnionLoabourModel::where($map)->order('create_time desc')->limit($total)->field('id,title,create_time,views')->select();

        }else {
            $lastId = input('lastId');

            $map['id'] = ['<', $lastId];
            $map['type'] = input('type');
            $total = input('total');
            $map['status'] = 1;

            $list = UnionModel::where($map)->order('create_time desc')->limit($total)->field('id,title,create_time,views')->select();
        }

        return json_encode($list);
    }



    public function unionDetail(){
        $map['id']=input('id');
        $res = UnionModel::where($map)->find();


        // 评论列表
        $map = [
            'target_id' => input('id')
        ];
        $comments = UnionComment::where($map)->order('id desc')->select();

        $list2=array();
        $count=count($comments);
        if($count>6){
            for($i=0;$i<6;$i++){
                array_push($list2,$comments[$i]);
            }
            foreach( $list2 as $v){
                $header =isset($v->wechatuser->header)?$v->wechatuser->header:"";
                if(!empty($header)){
                    $v['header']=$header;
                }else {
                    $v['header'] = isset($v->wechatuser->avatar) ? $v->wechatuser->avatar : "";
                }
            }}else{
            for($i=0;$i<$count;$i++){
                array_push($list2,$comments[$i]);
            }
            foreach( $list2 as $v){
                $header =isset($v->wechatuser->header)?$v->wechatuser->header:"";
                if(!empty($header)){
                    $v['header']=$header;
                }else {
                    $v['header'] = isset($v->wechatuser->avatar) ? $v->wechatuser->avatar : "";
                }
            }
        }

        // 是否已经收藏
        $collectMap = [
            'target_id' => input('id'),
            'user_id' => session('userId')
        ];
        $collect = Collect::where($collectMap)->find();
        //echo json_encode($comments);
        $this->assign('collect', json_encode($collect));

        // 添加阅读量
        UnionModel::where('id', input('id'))->setInc('views');

        $this->assign('count', $count);
        $this->assign('comments', json_encode($list2));
        $this->assign('news', $res);
        return $this->fetch();
    }

    public function loabourDetail(){
        $res = UnionLoabourModel::where('id','eq',input('id'))->find();
        $res['title']=$res['title'];
        $this->assign('res',json_encode($res));

        // 添加阅读量
       $res = UnionModel::where('id', input('id'))->setInc('views');



        return $this->fetch();
    }

    //评论分页
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