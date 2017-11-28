<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/11/20
 * Time: 上午9:11
 */

namespace app\index\model;


use think\Model;
use app\index\model\CompanyApplication;
class TrademarkInquire extends  Model
{

    protected $type = [
        'create_time' => 'strtotime',
        'end_time' => 'strtotime'
    ];


    public function user()
    {

        return  $this->hasOne('WechatUser', 'userid', 'userid');

    }

    public  function  InquireHistoryDetail($id,$appid){
        $info = $this->get($id);
        $app = CompanyApplication::Where('app_id', $appid)->find();
        $info['app_name'] = $app['name'];
        $info['submit_img'] = !empty($info['submit_img']) ? json_decode($info['submit_img']) : array();
        $info['back_img'] = !empty($info['back_img']) ? json_decode($info['back_img']) : array();
        $info['trademark_type']=1;

    return $info;
    }





}