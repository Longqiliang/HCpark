<?php
/**
 * Created by PhpStorm.
 * User: 贡7
 * Date: 2017/12/14
 * Time: 下午6:05
 */

namespace app\index\model;

use think\Model;

class Like extends Model
{
    /**
     * 判断用户是否点赞
     * @param $aid
     * @param $uid
     * @return int
     */
    public function isLike($aid,$uid){
        $map = ['aid' => $aid,'uid' => $uid ,'status' => 1];
        $info = $this->where($map)->find();
        if ($info){

            return 1;
        }else{

            return 0;
        }
    }
}