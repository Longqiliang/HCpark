<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/9/13
 * Time: 下午4:34
 */

namespace app\common\model;

use think\Model;

class ParkIntention extends Model
{
    public static function getNumforUndone()
    {
        $park_id = session('user_auth')['park_id'];
        $num = ParkIntention::where(['park_id' => $park_id, 'status' => 0])->count();
        return $num;
    }
}