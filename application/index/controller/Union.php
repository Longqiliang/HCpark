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

class Union extends Base
{
    public function index(){
        $map=[
            'status' => 1,
        ];
        $list1=UnionModel::where($map)->where('type',1)->order('create_time desc')->limit(2)->select();
        $list2=UnionLoabourModel::where($map)->order('create_time desc')->limit(2)->select();
        $list3=UnionModel::where($map)->where('type',2)->order('create_time desc')->limit(2)->select();

        $this->assign('list1',json_encode($list1));
        $this->assign('list2',json_encode($list2));
        $this->assign('list3',json_encode($list3));
        return $this->fetch();
    }
//通知公告和相关活动写一起，传我type1 type2
    public function unionList(){
        if(input('type')==3){
            $map['id'] = ['<', input('lastId')];
            $total = input('total', 1);
            $map['status'] = 1;

            $list = UnionLoabourModel::where($map)->order('create_time desc')->limit($total)->select();
        }else {
            $map['type'] = input('type');
            $map['id'] = ['<', input('lastId')];
            $total = input('total', 1);
            $map['status'] = 1;

            $list = UnionModel::where($map)->order('create_time desc')->limit($total)->select();
        }
        $this->assign('list',json_encode($list));
        return $this->fetch();
    }

    public function unionDetail(){
        $map=[
          'type'=>input('type'),
            'id'=>input('id'),
        ];
        $res = UnionModel::where($map)->find();
        $this->assign('res',json_encode($res));
    }

    public function loabourDetail(){
        $res = UnionLoabourModel::where('id','eq',input('id'))->find();
        $this->assign('res',json_encode($res));
    }


}