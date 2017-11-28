<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/11/20
 * Time: 上午9:12
 */

namespace app\index\model;


use think\Model;
use app\index\model\CompanyApplication;
class TrademarkAdvisory extends  Model
{


    protected  $type =[
        'create_time'=>'strtotime',
        'end_time'=>'strtotime'
    ];

    public  function  AdvisoryHistoryDetail($id,$appid){
        $info = $this->get($id);
        $app = CompanyApplication::Where('app_id', $appid)->find();
        $info['user_name']=$info['name'];
        $info['app_name'] = $app['name'];
        $info['trademark_type']=2;

        return $info;
    }
}