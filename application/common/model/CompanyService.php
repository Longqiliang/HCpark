<?php
/**
 * Created by PhpStorm.
 * User: shen
 * Date: 2017/8/30
 * Time: 下午1:43
 */
namespace app\common\model;


use think\Model;
use think\Db;
class CompanyService extends Model
{
    /**
     * 未完成的数量
     */
    public static  function  getNumforUndone(){
        $parkid = session('user_auth')['park_id'];
        $num = Db::table('tb_company_service')
            ->alias('s')
            ->join('__COMPANY_APPLICATION__ a', 's.app_id=a.app_id')
            ->join('__WECHAT_USER__ c', 'c.userid=s.user_id')
            ->field('a.name,s.id,s.company,s.people,s.mobile,s.remark,s.status,s.create_time')
            ->where('s.status',0)
            ->where('c.park_id',$parkid)
            ->count();

        return $num;
    }


}