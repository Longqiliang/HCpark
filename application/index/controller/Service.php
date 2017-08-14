<?php
/**
 * Created by PhpStorm.
 * User: Butaier
 * Date: 2017/8/14
 * Time: 17:55
 */
namespace app\index\controller;

class Service extends Base{

    public function index() {

        return $this->fetch();
    }

}