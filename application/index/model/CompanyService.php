<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/8/15
 * Time: 下午4:26
 */

namespace app\index\model;


use think\Model;
use app\index\model\CompanyApplication;

class CompanyService extends Model
{

    public function user()
    {

        return $this->hasOne('WechatUser', 'userid', 'user_id');

    }

    public function historyDetail($id, $app_id)
    {

        $info = $this->get($id);
        $app = CompanyApplication::Where('app_id', $app_id)->find();
        $info['app_name'] = $app['name'];
        return $info;
    }

}