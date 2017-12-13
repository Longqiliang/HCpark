<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/12/13
 * Time: 上午9:41
 */

namespace app\index\controller;


class Activity extends Base
{
    //活动报名主页
    public function index()
    {

        $this->fetch();
    }

    //活动报名详情页
    public function detail()
    {


        $this->fetch();
    }

    //报名
    public function signUp()
    {


        return $this->fetch('sign_up');
    }


}