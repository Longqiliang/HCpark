<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/12/13
 * Time: 上午9:41
 */

namespace app\index\controller;

use app\index\model\Activity as ActivityModel;
use app\index\model\ActivityComment;
use app\index\model\WechatUser;
use MongoDB\Driver\WriteError;

class Activity extends Base
{
    //活动报名主页
    public function index()
    {
        $activity = new ActivityModel();
        $list = $activity->getListbyParkid();
        $this->assign('list', json_encode($list));
        return $this->fetch();
    }

    //活动报名详情页
    public function detail()
    {
        $data = input('id');
        $info =ActivityModel::get($data['id']);
        $this->assign('info',json_encode($info));
        return $this->fetch();
    }

    //报名
    public function signUp()
    {   $data = input('');
        if(IS_POST){
            $activity  =  new ActivityModel();

            $data = $activity->save($data);
            if($data){
                return $this->success('报名成功');

            }else{
                return $this->error("保存失败");
            }


        }else{
         $userid =  session('userId');
         $user = WechatUser::where('userid',$userid)->find();
         $map=[
             'name'=>$user['name'],
             'department'=>isset($user->departmentName->name)?$user->departmentName->name:"",
             'mobile'=>$user['mobile'],
             'activity_id'=>$data['id']
         ];
         $this->assign('user',$map);

        }

        return $this->fetch('sign_up');
    }


}