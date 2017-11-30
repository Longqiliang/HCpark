<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/11/29
 * Time: 上午9:27
 */

namespace app\index\model;


use think\Model;

class ExchangePoint extends Model
{
    protected $type = [
        'create_time' => 'strotime'
    ];

    public function getPointHistorybyUserid()
    {
        $park_id = session('park_id');
        $userid = session('userId');
        $history = $this->where(['userid' => $userid, 'park_id' => $park_id, 'status' => ['neq', -1]])->select();
        return $history;
    }


}