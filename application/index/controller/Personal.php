<?php
/**
 * Created by PhpStorm.
 * User: zyf
 * QQ: 2205446834@qq.com
 * Date: 2017/6/2
 * Time: 13:35
 */

namespace app\index\controller;


use app\index\model\Collect as CollectModel;
use app\index\model\WechatUser;
use app\index\model\WechatDepartment;
use app\index\model\PersonalService;
class Personal extends Base
{
    public function  index(){
        $user_id= session("userId");
        $user = new  WechatUser();

        $userinfo = $user->checkUserExist($user_id);
        $this->assign("userinfo",$userinfo);

        return $this->fetch();
    }

    /*个人信息*/
    public function personalinfo(){


        $user_id = session('userId');
        $wu = new  WechatUser();
        $info =$wu->where('userid',$user_id)->find();
        $data=[
            'name'=>$info['name'],
            'avatar'=>$info['avatar'],
            'sex'=>$info['gender']==1?"男":"女",
            'mobile'=>$info['mobile'],
            'department'=>isset($info->departmentName->name)?$info->departmentName->name:"",
            'header'=>$info['header']
        ];

        $this->assign('info',$data);
        return $this->fetch();
    }
    /*个人收藏*/
    public function collection(){
        $user_id =session("userId");
        $list1 = CollectModel::where(['user_id'=>$user_id,'type'=>1])
            ->order('create_time desc')
            ->limit(6)->select();
        foreach ($list1 as $k=>$v){
            $list1[$k]['title'] = $v->News->title;
            $list1[$k]['views'] =$v->News->views;
            $list1[$k]['image'] =$v->News->frontCover;
        }

        $list2 = CollectModel::where(['user_id'=> $user_id,'type'=>2])
            ->order('create_time desc')
            ->limit(6)->select();
        foreach ($list2 as $k=>$v){
            $list2[$k]['title'] = $v->News->title;
            $list2[$k]['views'] =$v->News->views;
        }

        $list3 = CollectModel::where(['user_id'=>$user_id,'type'=>3])
            ->order('create_time desc')
            ->limit(6)->select();
        foreach ($list3 as $k=>$v){
            $list3[$k]['title'] = $v->News->title;
            $list3[$k]['views'] =$v->News->views;
        }
        $list4 = CollectModel::where(['user_id'=>$user_id,'type'=>['>',3]])
            ->order('create_time desc')
            ->limit(6)->select();
        foreach ($list4 as $k=>$v){
            $list4[$k]['title'] = $v->News->title;
            $list4[$k]['views'] =$v->News->views;
        }

        $this->assign('list1',$list1);
        $this->assign('list2',$list2);
        $this->assign('list3',$list3);
        $this->assign('list4',$list4);

        return $this->fetch();

    }
    /**党员信息*/
    public function party ()
    {
        $user_id = session("userId");
        $party_user=new  WechatUser();
        if (IS_POST) {
            $data=input('');
                $result =$party_user->save($data,['userid'=>$user_id]);
            if($result!==false){

                return $this->success('成功');
            }else{

                return $this->error('失败');
            }
        } else {
            $userInfo = $party_user->where(['userid' => $user_id])->find();
            if($userInfo){
                $result['data']=$userInfo;
            }
            $this->assign("data",json_encode($result) );
            return $this->fetch();
        }

    }

    /**
     * 设置头像
     */
    public function setHeader(){
        $userId = session('userId');
        $header = input('header');
        $map = array(
            'header' => $header,
        );
        $wu = new  WechatUser();
        $info = $wu->where('userid',$userId)->update($map);
        if($info){
            return $this->success("修改成功");
        }else{
            return $this->error("修改失败");
        }
    }
    /*我的消息*/
    public function message(){

        return  $this->fetch();
    }

    /*我的服务*/
    public function service(){
        $service = new   PersonalService();
        $userid = session('userId');
        $map=[
            'userid'=>$userid,
            'type'=>0
        ];
        //物业服务
        $list=$service ->where($map)->select();
        foreach ($list  as $value){
            if($value['status']==0){
                $value['status_text']='进行中';
            } else{
                $value['status_text']='已完成';
            }
        }
        $this->assign('property',$list);
        //企业服务
        $map['type']=1;
        $list=$service ->where($map)->select();
        foreach ($list  as $value){
            if($value['status']==0){
                $value['status_text']='进行中';
            } else{
                $value['status_text']='已完成';
            }
        }
        $this->assign('company',$list);
        $this->assign('list',$list);
        return  $this->fetch();
    }
    /*我的收藏下拉刷新*/
    public function listmore(){
        $len = input("length");
        $type =input("type");
        $user_id= session("userId");
        if ($type==1){
            $list1= CollectModel::where(['user_id'=>$user_id,'type'=>1])
                ->order('create_time desc')
                ->limit($len,6)->select();
            if ($list1){
                foreach ($list1 as $k=>$v){
                    $list1[$k]['title'] = $v->News->title;
                    $list1[$k]['views'] =$v->News->views;
                    $list1[$k]['image'] =$v->News->front_cover;
                    unset( $list1[$k]['News']);
                }

                return  json(['code'=>1,'data'=>$list1]);
            }else {

                return  json(['code'=>0,'msg'=>"没有更多内容了"]);
            }

        }else if($type==2){
            $list2 = CollectModel::where(['user_id'=> $user_id,'type'=>2])
                ->order('create_time desc')
                ->limit($len,6)->select();
            if ($list2){
                foreach ($list2 as $k=>$v){
                    $list2[$k]['title'] = $v->News->title;
                    $list2[$k]['views'] =$v->News->views;
                    unset( $list2[$k]['News']);
                }
                return  json(['code'=>1,'data'=>$list2]);
            }else {

                return  json(['code'=>0,'msg'=>"没有更多内容了"]);
            }
        }else if ($type==3){
            $list3 = CollectModel::where(['user_id'=>$user_id,'type'=>3])
                ->order('create_time desc')
                ->limit($len,6)->select();
            if ($list3){
                foreach ($list3 as $k=>$v){
                    $list3[$k]['title'] = $v->News->title;
                    $list3[$k]['views'] =$v->News->views;
                    unset( $list3[$k]['News']);
                }

                return  json(['code'=>1,'data'=>$list3]);
            }else {
                return  json(['code'=>0,'msg'=>"没有更多内容了"]);
            }
        }else {
            $list4 = CollectModel::where(['user_id'=>$user_id,'type'=>['>',3]])
                ->order('create_time desc')
                ->limit($len,6)->select();
            if ($list4){
                foreach ($list4 as $k=>$v){
                    $list4[$k]['title'] = $v->News->title;
                    $list4[$k]['views'] =$v->News->views;
                    unset( $list4[$k]['News']);
                }

                return  json(['code'=>1,'data'=>$list4]);
            }else {

                return  json(['code'=>0,'msg'=>"没有更多内容了"]);
            }
        }
    }
}