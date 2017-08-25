<?php
/**
 * Created by PhpStorm.
 * User: 虚空之翼
 * Date: 2017/4/9
 * Time: 下午5:27
 */

namespace app\index\controller;

use app\common\behavior\Service;
use app\index\model\WechatUser;
use think\Loader;
use think\Log;
use wechat\TPWechat;
use think\Controller;
use app\common\model\ThirdUser as ThirdUserModel;
use app\common\model\PayLog as PayLogModel;
use app\common\model\PointsLog as PointsLogModel;

class Wechat extends Controller
{
    public function index() {
        phpinfo();
    }

    public function callback() {
        $weObj = new TPWechat(config('wechat'));
        $weObj->valid();
    }

    public function valid() {
        $weObj = new TPWechat(config('scan'));
        $weObj->valid();
    }

    // 自动登入
    public function login(){
        Loader::import('wechat\TPWechat', EXTEND_PATH);
        $weObj = new TPWechat(config('company'));
        $userId = $weObj->getUserId(input('code'), config('company.agentid'));
//var_dump($userId);
//var_dump('errcode:'.$weObj->errCode.',msg:'.$weObj->errMsg);
        $userInfo = $weObj->getUserInfo($userId['UserId']);
        $data = [
            'userid' => $userInfo['userid'],
            'name' => $userInfo['name'],
            'mobile' => $userInfo['mobile'],
            'gender' => $userInfo['gender'],
            'avatar' => $userInfo['avatar'],
//            'department' => $userInfo['department'][0], //只选第一个所属部门
        ];

        $wechatUser = new WechatUser();
        if ($wechatUser->checkUserExist($userInfo['userid'])) {
            $wechatUser->save($data, ['userid' => $userInfo['userid']]);
        } else {
            $wechatUser->save($data);
        }
        
        session('userId', $userInfo['userid']);
        session('name', $userInfo['name']);
        session('gender', $userInfo['gender']);
        session('avatar', $userInfo['avatar']);

        // 默认跳转到前一页
        $this->redirect(session('requestUri'));
    }

    /**
     * 微信支付返回
     */
    public function notify() {

        //接受成功回调支付信息
        $xml = file_get_contents('php://input');
        $arr = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        Log::record($arr);
        $map = [
            'status' => 0,
            'order_id' => $arr['out_trade_no'],
        ];

        //修改订单
        $res = PayLogModel::where($map)->find();
        if ($res) {
            $res->status = 1;
            $res->save();

            //新增购买记录
            $data = [
                'user_id' => $res['user_id'],
                'user_type' => $res['user_type'],
                'type' => 2, //积分购买
                'amount' => $res['points'],
            ];
            PointsLogModel::create($data);

            //修改用户积分
            if ($res['user_type'] == 1) {

                WechatUser::where('userid',$res['user_id'])->setInc('points', $res['points']);
            } else if ($res['user_type'] == 2) {

                ThirdUserModel::where('user_id',$res['user_id'])->setInc('points', $res['points']);
            }

            //返回成功
            $return = ['return_code' => 'SUCCESS', 'return_msg' => 'OK'];
            $xml = '<xml>';
            foreach ($return as $k => $v) {
                $xml .= '<' . $k . '><![CDATA[' . $v . ']]></' . $k . '>';
            }
            $xml .= '</xml>';

            echo $xml;
        }

    }
}