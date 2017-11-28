<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/8/18
 * Time: 上午9:29
 */

namespace app\index\model;

use think\Model;

class PropertyServer extends Model
{

    protected $insert = [
        'create_time' => NOW_TIME

    ];


    protected $type = [

        'create_time' => 'strtotime'


    ];

    public function user()
    {


        return $this->hasOne('WechatUser', 'userid', 'user_id');
    }

    public static function getNumforUndone($type)
    {
        $parkid = session('user_auth')['park_id'];
        if ($type == 1) {
            //报修
            $num = PropertyServer::where(['park_id' => $parkid, 'type' => ['<', 4], 'status' => 0])->count();
        } else {
            //保洁
            $num = PropertyServer::where(['park_id' => $parkid, 'type' => 4, 'status' => 0])->order('id desc')->count();
        }
        return $num;
    }


}