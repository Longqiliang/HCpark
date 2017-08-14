<?php
/**
 * Created by PhpStorm.
 * User: Butaier
 * Date: 2017/8/14
 * Time: 14:09
 */
namespace app\index\controller;

class Enterprise extends Base{
    public function index() {
        return $this->fetch();
    }
}