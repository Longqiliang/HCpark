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
use app\index\model\Comment;
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
        $this->assign('res',json_encode($res));

        // 评论列表
        $map = [
            'target_id' => input('id')
        ];
        $comments = Comment::where($map)->order('id desc')->select();

        $list2=array();
        $count=count($comments);
        if($count>0){
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
            }}

        $this->assign('count', $count);
        $this->assign('comments', json_encode($list2));


        return $this->fetch();
    }

    public function loabourDetail(){
        $res = UnionLoabourModel::where('id','eq',input('id'))->find();
        $res['title']=$res['title'];
        $this->assign('res',json_encode($res));


        // 评论列表
        $map = [
            'target_id' => input('id')
        ];
        $comments = Comment::where($map)->order('id desc')->select();

        $list2=array();
        $count=count($comments);
        if($count>0){
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
            }}

        $this->assign('count', $count);
        $this->assign('comments', json_encode($list2));

        return $this->fetch();
    }


}