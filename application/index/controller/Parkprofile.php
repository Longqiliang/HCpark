<?php
/**
 * Created by PhpStorm.
 * User: shen
 * Date: 2017/9/2
 * Time: 下午3:53
 */
namespace app\index\controller;
use think\Controller;
use app\index\model\Park;

class Parkprofile extends Base
{
/*园区简介，前台加判断，如果没有全景图片，就不显示*/
    public function index(){
        $parkid = input('park_id');
        $map=array(
            'id'=>$parkid,
        );

        $info = Park::where($map)->field('panorama_cover,panorama_link,content')->find();
        $this->assign('info',json_encode($info));
        return $this->fetch();
    }


}