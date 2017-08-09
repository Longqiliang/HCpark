<?php
namespace app\admin\controller;

use think\Loader;
use wechat\TPWechat;
use app\common\model\WechatDepartment;
use app\common\model\WechatTag;
use app\index\model\WechatUser;
use app\common\behavior\Service;

class Index extends Admin {

    public function index() {

        return $this->fetch();
    }

    public function test() {

        Service::sendNewsMessage(111, '18969030101');
    }

    // 同步用户
    public function syncUser() {
        Loader::import('wechat\TPWechat', EXTEND_PATH);
        $weObj = new TPWechat(config('sync'));

        // 更新部门信息
        $department = $weObj->getDepartment();
        foreach($department['department'] as $data){
            $users = $weObj->getUserListInfo($data['id'], 0, 1);
            foreach ($users['userlist'] as $user) {
                $data = [
                    'userid' => $user['userid'],
                    'name' => $user['name'],
                    'mobile' => $user['mobile'],
                    'gender' => $user['gender'],
                    'avatar' => $user['avatar'],
                    'department' => $user['department'][0], //只选第一个所属部门
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
        $weObj = new TPWechat(config('sync'));
        // 更新部门信息
        $department = $weObj->getDepartment();

        $dep = new WechatDepartment();
        foreach($department['department'] as $data){
            $isUpdate = false;
            if (WechatDepartment::get($data['id'])) {
                $isUpdate = true;
            }
            $dep->data($data,true)->isUpdate($isUpdate)->save();
        }

        $this->success('同步部门成功！');
    }
    // 同步标签
    public function syncTag() {
        Loader::import('wechat\TPWechat', EXTEND_PATH);
        $weObj = new TPWechat(config('sync'));
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
}