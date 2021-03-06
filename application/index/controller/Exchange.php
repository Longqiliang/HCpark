<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/11/15
 * Time: 上午9:25
 */

namespace app\index\controller;

use app\index\model\ExchangePoint;
use app\index\model\WechatUser;
use app\index\model\ExchangeProduct;
use app\index\model\ExchangeRecord;

class Exchange extends Base
{
    /**
     * 积分商城商品列表
     */
    public function index()
    {
        $user = new WechatUser();
        $prak_id = session('park_id');
        $record = new ExchangeRecord();
        $product = new ExchangeProduct();
        $userid = session('userId');
        $userinfo = $user->where('userid', $userid)->find();
        $Product = $product->getAllProductOrderbyId($prak_id);
        $map = [
            'score' => $userinfo['score'],
            'product' => $Product
        ];
        $this->assign('score', json_encode($map));
        return $this->fetch();
    }

    /**
     * 用户兑换记录
     */
    public function record()
    {

        $userid = session('userId');
        $record = new ExchangeRecord();
        $point = new ExchangePoint();
        $list1 = $point->getPointHistorybyUserid();
        $list = $record->getRecordList($userid);
        //type 1 积分减少 2 积分增加
        $info=array();
        foreach ($list as $key =>$value){
            $info[$key]['id']=$value['id'];
            $info[$key]['title']=$value['title'];
            $info[$key]['create_time']=date('Y-m-d',$value['create_time']);
            $info[$key]['score']=$value['need_score'];
            $info[$key]['type']=1;
            $info[$key]['status']=$value['status'];
        }
        foreach ($list1 as $value){
         $map=[
             'title'=>$value['content'],
             'create_time'=>date('Y-m-d',$value['create_time']),
             'score'=>$value['score'],
             'type'=>$value['type'],
         ];
        array_push($info ,$map);
        }

        $info = list_sort_by($info,'create_time','desc');

        //echo json_encode($list);
        //echo json_encode($info);
        $this->assign('list', json_encode($info));
        return $this->fetch();
    }

    /**
     * 积分规则
     */
    public function rule()
    {
        return $this->fetch();
    }

    /**
     * 用户兑换记录详情
     */
    public function recordInfo()
    {
        $record_id = input('record_id');
        $record = new ExchangeRecord();
        $list = $record->getRecordInfo($record_id);
        $list['title'] = isset($list->productInfo->title) ? $list->productInfo->title : "";
        $this->assign('info', json_encode($list));
        return $this->fetch();
    }

    /**
     * 商品详情
     */
    public function productInfo()
    {
        $product = new ExchangeProduct();
        $record = new ExchangeRecord();
        $user = new WechatUser();
        $userid = session('userId');
        if (IS_POST) {
            $data = input('');
            if ($data['num'] < 0) {
                return $this->error("数量不能为负");
            }
            //开启事务
            $product->startTrans();
            $userinfo = $user->where('userid', $userid)->find();  //  获取总积分
            // 更新产品表
            //查询并锁表（product）
            $res = $product->getLockProduct($data['product_id']);
            $sum = $data['num'] * $res['price'];
            if ($userinfo['score'] < $sum) {
                $product->rollback();
                return $this->error('积分不足', '', $res['left']);
            }
            if ($res->left < $data['num']) {
                //事务回滚
                $product->rollback();
                return $this->error('商品数量不足', '', $res['left']);
            }
            $temp = $res->left - $data['num'];
            //更新那一行表数据（left=left-num）
            $result = $product->updateLeft($data['product_id'], $temp);
            if ($result) {
                // 产品表更新成功 存储兑换记录表
                $info['userid'] = $userid;
                $info['product_id'] = $data['product_id'];
                $info['content'] = $sum;
                $info['num'] = $data['num'];
                $info['create_time'] = time();
                $info['need_score'] = $sum;
                $info['left_score'] = $userinfo['score'] - $sum;
                $info['commodity_code'] = $this->random();
                $info['park_id'] = session('park_id');
                $flag = $record->save($info);
                //购买成功后
                if ($flag) {
                    $userinfo['score'] = $userinfo['score'] - $sum;
                    $re = $userinfo->save();
                    if ($re) {
                        $product->commit();
                        $map = ['commodity_code' => $info['commodity_code'], 'left' => $res['left'] - $data['num']];
                        return $this->success('成功', '', $map);
                    } else {
                        $product->rollback();
                        return $this->error("更新用户剩余积分错误", '', $res['left']);
                    }
                } else {
                    $product->rollback();
                    return $this->error("更新兑换记录表失败", '', $res['left']);
                }
            } else {
                $product->rollback();
                return $this->error("更新商品数量失败", '', $res['left']);
            }

        } else {
            $product_id = input('product_id');
            $re = $product->getPoductInfoById($product_id);
            $userinfo = $user->where('userid', $userid)->find();  //  获取总积分
            $data = ['id' => $re['id'],
                'title' => $re['title'],
                'product_img' => $re['product_img'],
                'content' => $re['content'],
                'num' => $re['num'],
                'price' => $re['price'],
                'score' => $userinfo['score'],
                'left' => $re['left'],
            ];
            $this->assign('info', json_encode($data));
            return $this->fetch();
        }
    }

    public function _safegs($data)
    {
        //格式处理
        //1.剥去标签
        $content2 = strip_tags($data);
        //2.获取中文字符
        $des1 = mb_substr($content2, 0, 60);
        //3.替换（这里是空格）
        $content1 = str_replace("&nbsp;", "", $des1);
        return $content1;

    }


    /**
     * 随机字符
     * @param number $length 长度
     * @param string $type 类型
     * @param number $convert 转换大小写
     * @return string
     */
    public function random($length = 6, $type = 'string', $convert = 0)
    {
        $config = array(
            'number' => '1234567890',
            'letter' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
            'string' => 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789',
            'all' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890'
        );

        if (!isset($config[$type])) $type = 'string';
        $string = $config[$type];

        $code = '';
        $strlen = strlen($string) - 1;
        for ($i = 0; $i < $length; $i++) {
            $code .= $string{mt_rand(0, $strlen)};
        }
        if (!empty($convert)) {
            $code = ($convert > 0) ? strtoupper($code) : strtolower($code);
        }
        //校验是否重复
        $record = new ExchangeRecord();
        $re = $record->where('commodity_code', $code)->find();
        if ($re) {
            return $this->random();
        } else {
            return $code;
        }


    }


}




