<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/11/15
 * Time: 上午9:25
 */

namespace app\index\controller;

use app\index\model\WechatUser;
use app\index\model\ExchangeProduct;
use app\index\model\ExchangeRecord;

class Exchange extends Base
{
    //积分商城商品列表
    public function index()
    {
        $user = new WechatUser();
        $record = new ExchangeRecord();
        $product = new ExchangeProduct();
        $userid = session('userId');
        $userinfo = $user->where('userid', $userid)->find();
        $Product = $product->getAllProductOrderbyId();
        $map = [
            'score' => $userinfo['score'],
            'product' => $Product
        ];
        $this->assign('score',json_encode($map));
       return $this->fetch();


    }


}