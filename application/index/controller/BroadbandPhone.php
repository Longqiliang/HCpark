<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/17
 * Time: 9:33
 */
namespace app\index\controller;

use app\index\model\BroadbandPhone as BroadbandModel;
use app\index\model\WechatUser;
class BroadbandPhone extends Base
{
    public function index()
    {
        if ($_POST) {
            $broadbandModel = new BroadbandModel;
            $_POST['user_id']=session('userId');

            $result = $broadbandModel->allowField(true)->validate(true)->save($_POST);
            if ($result) {
                //预约成功跳转到结果页面
                $this->redirect('broadband_phone/results');
            } else {
                $this->error($broadbandModel->getError());
            }
        } else {
            return $this->fetch();
        }
    }


}