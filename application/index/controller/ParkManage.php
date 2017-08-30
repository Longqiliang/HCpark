<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/8/30
 * Time: 下午6:03
 */

namespace app\index\controller;

//园区管理
class ParkManage extends  Base
{
    //主界面
    public  function index(){


      return $this->fetch();
   }


}