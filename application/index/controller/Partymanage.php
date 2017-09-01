<?php
namespace app\index\controller;
use app\index\model\WechatUser;
use think\Controller;
use wechat\TPWechat;
use wechat\TPQYWechat;
use think\Loader;
use app\common\model\CompanyContract;
use app\index\model\Park;
use app\index\model\WechatTag;
class Partymanage extends Base
{
    /** 园区管理首页 **/
    public function index(){

        return $this->fetch();
    }

    /** 园区统计 **/
    public function statistics(){
        $userid=session('userId');
        $park_id=session('park_id');
        $id=input('id');
        $park = new Park();
        $list = $park->select();
        foreach ($list as $k=>$v){
            $data[$k] = [
                'name' => $v['name'],
                'address' => $v['address'],

            ];
        }
        $tagid=WechatUser::where('userid','eq',$userid)->field('tagid')->find();

            if ($tagid['tagid'] == 1) {
                $res = Park::field('id,name,area_total,area_use,area_other,scale_one,scale_two,scale_three,type_one,type_two,type_three')->select();
                if(input('id')){
                    $res = Park::where('id','eq',$id)->field('id,name,area_total,area_use,area_other,scale_one,scale_two,scale_three,type_one,type_two,type_three')->select();
                }
            } else {
                $res = Park::where('id', 'eq', $park_id)->field('id,name,area_total,area_use,area_other,scale_one,scale_two,scale_three,type_one,type_two,type_three')->find();
                $res = array($res);
            }

        $this->assign('list',$data);
        $this->assign('res',json_encode($res));
        return $this->fetch();
    }
    /***合同管理*/
    public function contract(){
        $data[0] = CompanyContract::where(["park_id"=>session("park_id"),'type'=>1])->count();
        $data[1] = CompanyContract::where(["park_id"=>session("park_id"),'type'=>2])->count();
        $contract[0] = $data[0] + $data[1];
        $contract[1] = $data[0];
        $contract[2] = $data[1];
        $array=[
            'total'=>['name'=>"总合同数",'count'=>$contract[0]],
            'rent'=>['name'=>"租赁合同",'count'=>$contract[1]],
            'property'=>['name'=>"物业合同",'count'=>$contract[2]]
        ];
            return json_encode($array);
        $this->assign('info',json_encode($array));

        return $this->fetch();
    }
    /*合同列表*/
    public function managelist(){
        $type = input("type");
        $list = CompanyContract::where(["park_id"=>session("park_id"),'type'=>$type])
                                    ->order("create_time desc")
                                    ->limit(6)
                                    ->select();
        $name = "";
        switch ($type){
            case 1 :
                $name = "租赁合同";
                break;
            case 2 :
                $name = "物业合同";
                break;
            case 3 :
                $anme = "其他合同";
                break;
        };
        $this->assign('list',$list);
        $this->assign('name',$name);

        return $this->fetch();
    }
    /*合同下拉刷新*/
    public function listManage(){
        $type = input("type");
        $len = input("length");
        $list = CompanyContract::where(["park_id"=>session("park_id"),'type'=>$type])
            ->order("create_time desc")
            ->limit($len,6)
            ->select();
        if ($list){

            return json(['code' => 1, 'data' => json_encode($list)]);
        }else{

            return json(['code' => 0, 'msg' =>"没有更多内容了"]);
        }

    }
    /*合同详情*/
    public function manageDetail(){
        $id = input('id');
        $manageInfo = CompanyContract::get($id);
        foreach($manageInfo as $v){
            $v['img'] = unserialize($v['img']);

        }
        $info = [
            'extra'=>$manageInfo['remark'],
            'img'=>unserialize($manageInfo['img'])
        ];
        $this->assign('info', json_encode($info) );
    }
    /*招商管理*/
    public function merchants(){
    $userid=session('userId');
    $weuser=new WechatUser();
    $user=$weuser->where('userid',$userid)->find();
    $is_boss=$user['tagid']==1?"yes":"no";
    $this->assign('is_boss',$is_boss);
    return $this->fetch();




   }
    /*园区列表*/
    public function parkList(){
        $park = new Park();
        $list = $park->select();
        foreach ($list as $k=>$v){
            $data[$k] = [
                'name' => $v['name'],
                'address' => $v['address'],

            ];
        }
        $this->assign('list',$data);

        return $this->fetch();
    }

















}
