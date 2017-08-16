<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/8/16
 * Time: 下午2:08
 */
namespace app\admin\controller;

use app\admin\controller\Property as PropertyModel;

class Property extends Admin
{
    public function index(){



        $list="";
        $this->assign("list",$list);
        return $this->fetch();
    }




}