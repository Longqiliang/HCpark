<?php
/**
 * Created by PhpStorm.
 * User: 虚空之翼 <183700295@qq.com>
 * Date: 16/5/11
 * Time: 10:00
 */
namespace app\admin\model;

use think\Model;

class ActionLog extends Model
{
    protected $insert = [
        'action_id' => 0,
        'create_time' => NOW_TIME,
        'status' => 1,
        'record_id' => 0,
        'action_ip',
    ];

    protected function setActionIpAttr(){
        return request()->ip(1);
    }


}