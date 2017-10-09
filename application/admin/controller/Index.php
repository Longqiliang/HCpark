<?php
namespace app\admin\controller;

use app\common\model\Park;
use think\Loader;
use wechat\TPWechat;
use app\common\model\WechatDepartment;
use app\common\model\WechatTag;
use app\index\model\WechatUser;
use app\common\behavior\Service;
use app\common\model\CompanyContract;
use app\common\model\ParkCompany;
class Index extends Admin {

    public function index() {
        //首页所选园区ID
        $id = session('user_auth')['park_id'];

        if ($id == '1') {
            //园区统计
            $res = Park::field('id,area_total,area_use,area_other,scale_one,scale_two,scale_three,type_one,type_two,type_three')->find();
            $list = Park::field('id,name,address,images')->select();

        } else {
            $res = Park::where('id', 'eq', $id)->field('id,area_total,area_use,area_other,scale_one,scale_two,scale_three,type_one,type_two,type_three')->find();
            $list = Park::where('id', 'eq', $id)->field('id,name,address,images')->select();
        }

        $data[0] = CompanyContract::where(["park_id" => $id, 'type' => 1,'status'=>0])->count();
        $data[1] = CompanyContract::where(["park_id" => $id, 'type' => 2,'status'=>0])->count();
        $data[2] = CompanyContract::where(["park_id" => $id, 'type' => ['>', 2],'status'=>0])->count();
        $contract[0] = $data[0] + $data[1] + $data[2];
        $contract[1] = $data[0];
        $contract[2] = $data[1];
        $contract[3] = $data[2];
        $array = [
            'count' => $contract[0],
            'rent' => $contract[1],
            'property' => $contract[2],
            'other' => $contract[3],
        ];




//        return json_encode($array);

        $this->assign('info', json_encode($array));
//        echo json_encode($list);


        $this->assign('list', json_encode($list));
        $this->assign('res', json_encode($res));
        return $this->fetch();
    }

    public function test() {

        Service::sendNewsMessage(111, '18969030101');
    }

    // 同步用户
    public function syncUser() {
        Loader::import('wechat\TPWechat', EXTEND_PATH);
        $weObj = new TPWechat(config('party'));

        // 更新部门信息
        $department = $weObj->getDepartment();
        foreach($department['department'] as $data){
            $users = $weObj->getUserListInfo($data['id'], 0, 1);
            foreach ($users['userlist'] as $user) {
                $id =$this->findParkid($user['department'][0]);
                $data = [
                    'userid' => $user['userid'],
                    'name' => $user['name'],
                    'mobile' => $user['mobile'],
                    'gender' => $user['gender'],
                    'avatar' => $user['avatar'],
                    'department' => $user['department'][0], //只选第一个所属部门
                    'park_id'=>$id
                ];
                $wechatUser = new WechatUser();
                if ($wechatUser->checkUserExist($user['userid'])) {
                    $wechatUser->save($data, ['userid' => $user['userid']]);
                } else {
                    $wechatUser->save($data);
                }
            }
        }
        
        $this->success('同步用户成功！');
    }

    // 获取部门
    public function syncDepartment() {
        Loader::import('wechat\TPWechat', EXTEND_PATH);
        $weObj = new TPWechat(config('party'));
        // 更新部门信息
        $department = $weObj->getDepartment();
        $dep = new WechatDepartment();
        foreach($department['department'] as $k=>$data){
            $isUpdate = false;
            if (WechatDepartment::get($data['id'])) {
                $isUpdate = true;
            }
            $dep->data($data,true)->isUpdate($isUpdate)->save();
            $number[$k] =$data['id'];
        }

        $deaprtment=$dep->select();

        foreach ($deaprtment as $k=>$v){
            $deaprtmentNumber[$k] = $v['id'];
        }
        $deleteId =[] ;
        foreach($deaprtmentNumber as $v){
            if (!in_array($v,$number)){
                $deleteId[]=$v;
            }
        }
        foreach($deleteId as $v){
            WechatDepartment::where(['id'=> $v])->delete();
        }
        /*同步园区表*/
        $parkList=WechatDepartment::where(['parentid'=> 1])->select();
        $park =new Park();
        foreach($parkList as $k=>$v){
            $data =[
                'id'=>$v['id'],
                'name'=>$v['name'],
            ];
            $numberPark[$k] =$v['id'];
            $isUpdate = false;
            if (Park::get($data['id'])) {
                $isUpdate = true;
            }
            $res=$park->data($data,true)->isUpdate($isUpdate)->save();
        }
        $parks=$park->select();
        foreach($parks as $k=>$v){

            $parksNumber[$k] =$v['id'];
        }
        $delete =[];
        foreach($parksNumber as $v){
            if (!in_array($v, $numberPark)){
                $delete[] =$v;
            }
        }
        foreach($delete as $v){
            Park::where("id",$v)->delete();
        }
       //同步园区企业列表
        $deleteId=[];
        $parkCompany =new ParkCompany();
        $companyList=WechatDepartment::where(['parentid'=>4])->select();
        foreach ($companyList as $k=>$v){
            $parkid =$this->findParkid($v['id']);
            $data=[
                'id'=>$v['id'],
                'name'=>$v['name'],
                'park_id'=>$parkid,
                'company_id'=>$v['id'],
            ];
            $number[$k]=$v['id'];
            $isUpdate = false;
            if (ParkCompany::get($data['id'])) {
                $res=$parkCompany->where('id',$data['id'])->update($data);

            }else{
                $res=$parkCompany->data($data,true)->isUpdate($isUpdate)->save();
            }
        }
        $parkNumber=ParkCompany::where(['park_id'=>$parkid])->select();
        foreach($parkNumber as $k=>$v){
            $companyNumber[$k]=$v['id'];
        }
        foreach ($companyNumber as $v){
            if (!in_array(intval($v), $number)){
                $deleteId[] =$v;
            }
        }
        foreach($deleteId as $v){
            ParkCompany::where(['id'=> $v])->delete();
        }





        $this->success('同步部门成功！');
    }
    // 同步标签
    public function syncTag() {
        Loader::import('wechat\TPWechat', EXTEND_PATH);
        $weObj = new TPWechat(config('party'));
        $list = $weObj->getTagList();

        // 更新所有标签
        $tag = new WechatTag();
        foreach($list['taglist'] as $data){
            $isUpdate = false;
            if (WechatTag::get($data['tagid'])) {
                $isUpdate = true;
            }
            $tag->data($data,true)->isUpdate($isUpdate)->save();
        }

        // 更新用户标签
        $user = new WechatUser();
        foreach($list['taglist'] as $data) {
            $userList = $weObj->getTag($data['tagid']);
            foreach($userList['userlist'] as $value) {
                $user->where('userid', $value['userid'])->update(['tagid'=>$data['tagid']]);
            }
        }

        $this->success('同步标签成功！');
    }

    //
    public function pay() {
        $config = config('pay');
        $weObj = new TPWechat(config('pay'));
        $data = [
            'userid' => '18969030101',
//            'agentid' => $config['agentid']
        ];
        $user = $weObj->convertToOpenId($data);
        var_dump($user['openid']);
        echo 'code:'.$weObj->errCode.'，msg:'.$weObj->errMsg;

//        $payObj = new Weixinpay(config('weixinpay'));
//
//        $data = [
//            'body' => 'aaa',
//            'out_trade_no' => 'aaaa',
//            'total_fee' => 1,
//            'spbill_create_ip' => '1',
//            'trade_type' => 'JSAPI',
//            'openid' => $user['openid']
//        ];
//        $result = $payObj->unifiedOrder($data);
//        var_dump($result);

    }
      //查找园区id
     public  function  findParkid($Department){
         if($Department==1){
             return 1;
         }
         $WeDepartment=new WechatDepartment();
         $de = $WeDepartment->where('id',$Department)->find();
        if($de['parentid']==1){
            return $de['id'];
        }else{
            return $this->findParkid($de['parentid']);
        }
     }

}