<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/17
 * Time: 9:33
 */
namespace app\index\controller;

use app\index\model\WaterService as WaterModel;
use app\index\model\WechatUser;
use think\Db;
class WaterService extends Base
{
    public function index()
    {
        if ($_POST) {
            $waterModel = new WaterModel;
            $_POST['userid']=session('userId');
            $result = $waterModel->allowField(true)->validate(true)->save($_POST);
            if ($result) {
                //预约成功跳转到结果页面
                $this->redirect('water_service/results');
            } else {
                $this->error($waterModel->getError());
            }

        } else {
            $userid = session('userId');
            $contact = WechatUser::where('userid', 'eq', $userid)->field('name,mobile')->find();
            $this->assign('contact', $contact);
            return $this->fetch();
        }
    }

//送水记录列表页
    public function waterList(){
        //分页total
        $total=input('total');
        $userid = session('userId');
        $map = [
            'status'=> "1",
            'userid'=>$userid,
        ];
        $list = WaterModel::where($map)->order('id desc')->paginate($total);
        $this->assign('list',$list);
        return $this->fetch();
    }

    //送水记录详情页
    public function detail(){
        $id=input('id');
        $result=WaterModel::where('id','eq',$id)->find();
        $this->assign('res',$result);
        echo json_encode($result);exit;
        return $this->fetch();
    }



}