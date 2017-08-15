<?php
/**
 * Created by PhpStorm.
 * User: Butaier
 * Date: 2017/8/14
 * Time: 14:09
 */
namespace app\index\controller;
use app\index\model\ParkCompany;


//园区企业
class Enterprise extends Base{

    //企业列表
    public function index() {
        $park_id=session('park_id');
        $parkcompany= new ParkCompany();
        $list = $parkcompany->where('park_id',$park_id)->select();
        $this->assign('list',$list);
        return $this->fetch();

    }






}