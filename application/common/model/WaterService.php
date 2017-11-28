<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/15
 * Time: 10:16
 */
namespace app\common\model;


use think\Model;
use think\Db;

class WaterService extends Model
{

    public function watertype()
{
    return $this->hasOne("WaterType", "id","water_id" );
}

    public  static  function getNumforUndone()
    {   $parkid = session('user_auth')['park_id'];
        $num = Db::table('tb_water_service')
            ->alias('s')
            ->join('__WATER_TYPE__ t', 't.id=s.water_id')
            ->field('s.id,s.userid,s.name,s.mobile,s.address,s.number,s.create_time,s.status,s.check_remark,s.price totalprice,t.water_name,t.format ,t.price ')
            ->where('s.park_id', 'eq', $parkid)
            ->where('s.status <2 and s.status>-1')
            ->count();
       return $num;
    }




}