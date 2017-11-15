<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/11/13
 * Time: 下午4:42
 */

namespace app\admin\controller;
use app\common\model\ParkCompany;
use app\common\model\WechatUser;
class Manageuser extends  Admin{


public  function  index(){

    $parkid = session("user_auth")['park_id'];
    $map = ['park_id' => $parkid ];
    $search = input('search');
    if (!empty($search)) {
        $map['name'] = ['like', "%$search%"];
    }
    $companyList = ParkCompany::where($map)->order('id  asc')->paginate();

    /// dump($companyList);
    $this->assign('list', $companyList);

    return $this->fetch();

}

    /*权限管理*/
    public function manageuser()
    {
        $id = input('id');
        $list = WechatUser::where(['department'=> $id,'status'=>1])->order('id asc')->paginate();

        $this->assign('list', $list);
        return $this->fetch();
    }

    public function  edit(){
        $data = input('');
        $wechat = new WechatUser();
        $reult = $wechat->save($data,['userid'=>$data['userid']]);
       if($reult){
           return $this->success('成功');
       }else{
           return $this->error('失败');
       }


    }


}

