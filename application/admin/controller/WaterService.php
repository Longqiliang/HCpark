<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/17
 * Time: 9:33
 */
namespace app\admin\controller;

use app\common\model\WaterService as WaterModel;
use app\common\model\WechatUser;
use think\Db;
use app\common\behavior\Service as ServiceModel;
class WaterService extends Admin
{
    public function index()
    {
        $parkid =session("user_auth")['park_id'];
        $search = input('search');
        $map['s.status']  = ['neq',-1];
        if ($search != '') {
            $map['s.name'] = ['like','%'.$search.'%'];
        }
        $list = Db::table('tb_water_service')
            ->alias('s')
            ->join('__WECHAT_USER__ w', 's.userid=w.userid')
            ->field('s.id,s.userid,s.name,s.mobile,s.address,s.number,s.create_time,s.status,s.check_remark')
            ->where('w.park_id','eq',$parkid)
            ->where($map)
            ->order('create_time desc')
            ->paginate();

        $this->assign('list',$list);
        return $this->fetch();
    }

//详情页
    public function detail(){
        $id=input('id');
        $result=WaterModel::where('id','eq',$id)->find();
        $this->assign('res',$result);
        return $this->fetch();
    }

//逻辑删除
    public function moveToTrash() {
        $ids = input('ids/a');
        $result = WaterModel::where('id', 'in', $ids)->update(['status' => -1]);
        if($result) {
            return $this->success('删除成功', url('WaterService/index'));
        } elseif(!$result) {
            return $this->error('删除失败');
        }
    }

    //完成
    public function complete() {

        $id = input('id');
        $uid = input('uid');
        $remark = input('check_remark');
        $result = WaterModel::where('id', 'in', $id)->update(['status' => $uid,'check_remark'=>$remark]);
        $data = WaterModel::get($id);
        $userId = $data['userid'];
        if ($result){
            if ($uid ==1){
                $message = [
                    "title" => "饮水服务提示",
                    "description" => "送水地点：" . $data['address'] . "\n送水桶数：" . $data['number'] . "\n联系人员：" . $data['name'] . "\n联系电话：" . $data['mobile'],
                    "url" => 'http://' . $_SERVER['HTTP_HOST'] . '/index/service/historyDetail/appid/3/can_check/yes/id/'.$id,
                ];
            }else{
                $message = [
                    "title" => "饮水服务提示",
                    "description" => date('m月d日',time())."\n饮水服务暂时无法提供\n备注:".$data['check_remark'],
                    "url" => 'http://' . $_SERVER['HTTP_HOST'] . '/index/service/historyDetail/appid/3/can_check/yes/id/'.$id,
                ];

            }

            ServiceModel::sendPersonalMessage($message,18867514826);
            $this->success("已审核");
        }else{

            $this->error("未成功");
        }
    }



}