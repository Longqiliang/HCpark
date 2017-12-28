<?php
/**
 * Created by PhpStorm.
 * User: Scy
 * QQ: 329880211@qq.com
 * Date: 2016/12/9
 * Time: 10:38
 */

namespace app\index\controller;

use app\admin\model\Comments;
use app\admin\model\PushMessage;
use app\index\model\Card as CardModel;
use app\index\model\Picture;
use EasyWeChat\Core\Exception;
use app\index\model\CardType;
use think\Db;
use think\Log;
use app\index\model\Like;

/**
 * 帖子模块
 *
 * Class Card
 * @package app\card\controller
 */
class Card extends Base
{
    /**
     * 获取用户帖子
     *
     * @return \think\response\Json
     */
    public function getUserCard()
    {
        $uid = session("userId");
        $card_model = new CardModel();
        $cardType = new CardType();
        $like = new Like();
        //$map1 = ['uid' => $uid, 'status' => 1];
        //$list1 = $card_model->where($map1)->order("id desc")->limit(6)->select();
        $map2 = ['uid' => $uid, 'status' => 0];
        $list = $card_model->where($map2)->order("id desc")->limit(6)->select();

        //return json_encode(['check' => $list1, "uncheck" => $list2]);
        //$this->assign('list',json_encode(['check' => $list1, "uncheck" => $list2]));
        foreach ($list as $k => $v) {
            $list[$k]['name'] = isset($v->getUserHeader->name)?$v->getUserHeader->name:"";
            //$list[$k]['create_time'] = date('Y-m-d H:i', $v['create_time']);
            $list[$k]['header'] = isset($v->getUserHeader->avatar) ? $v->getUserHeader->avatar : '';
            $list[$k]['type'] = json_decode($list[$k]['type']);
            $list[$k]['type'] = $cardType->getCardTypeById( $list[$k]['type']);
            $list[$k]['list_img'] = !empty($v['list_img']) ? json_decode($v['list_img']) : "";
            unset($list[$k]['getUserHeader']);
            $list[$k]['like'] = $like->isLike($list[$k]['id'],$uid);
        }
        $list2 = $this->myComments();
        $this->assign('empty','<img class="empty" src="/index/images/service/card/icon-default.jpg">');
        $this->assign('list',json_encode($list));
        $this->assign('list2',json_encode($list2));

        return $this->fetch("personal/mycard");

    }

    /**
     * 下拉刷新用户帖子
     */
    public function getMoreUserCard()
    {
        $park_id = session("park_id");
        $card_model = new CardModel();
        $cardType = new CardType();
        $like = new Like();
        //$type = input('type');
        $uid = session("userId");
        $len = input('len');
        $map = ['uid' => $uid,'status' => 0,'park_id' => $park_id];
        $list = $card_model->where($map)->order("id desc")->limit($len,6)->select();
        foreach ($list as $k => $v) {
            $list[$k]['name'] = isset($v->getUserHeader->name)?$v->getUserHeader->name:"";
           // $list[$k]['create_time'] = date('Y-m-d H:i', $v['create_time']);
            $list[$k]['header'] = isset($v->getUserHeader->avatar) ? $v->getUserHeader->avatar : '';
            $list[$k]['type'] = json_decode($list[$k]['type']);
            $list[$k]['type'] = $cardType->getCardTypeById( $list[$k]['type']);
            $list[$k]['list_img'] = !empty($v['list_img']) ? json_decode($v['list_img']) : "";
            unset($list[$k]['getUserHeader']);
            $list[$k]['like'] = $like->isLike($list[$k]['id'],$uid);
        }

        return $this->success('获取成功！','',$list);

    }
    public function myComments(){
        $uid = session("userId");
        $comment_model = new Comments();
        $len = input("len",0);
        $card_model = new CardModel();
        $map = ['uid' => $uid];
        $list = $comment_model->where($map)->order('create_time desc')->limit($len,6)->select();
        foreach($list as $k => $v){
            $list[$k]['user_name'] = isset($v->getUserHeader->name)?$v->getUserHeader->name:"";
            //$list[$k]['create_time'] = date('Y-m-d H:i', $v['create_time']);
            $list[$k]['header'] = isset($v->getUserHeader->avatar) ? $v->getUserHeader->avatar : '';
            $article_id = $v['aid'];
            $articleInfo = $card_model->where(['id' => $article_id])->find();
            $list[$k]['card_content'] = $articleInfo['content'];
            $list[$k]['card_name'] = isset($articleInfo->getUserHeader->name)?$articleInfo->getUserHeader->name:"";
            unset($list[$k]['getUserHeader']);
        }
        //return json_encode($list);
        if (IS_POST) {
            return $this->success('获取成功！','',$list);
        } else {
            return $list;
        }
    }

    /**
     * 获取列表
     *
     * @return \think\response\Json
     */
    public function getList()
    {
        $len = input('len', 0);
        $uid = session("userId");
        $like = new Like();
        $card_model = new CardModel();
        $cardType = new CardType();
        $park_id = session("park_id");
        $map = ['park_id' => $park_id, 'status' => 0];
        $list = $card_model->where($map)->order("is_top desc,top_time desc,id  desc")->limit($len, 6)->select();
        foreach ($list as $k => $v) {
            $list[$k]['name'] = isset($v->getUserHeader->name)?$v->getUserHeader->name:"";
//            $list[$k]['create_time'] = date('Y-m-d H:i', $v['create_time']);
            $list[$k]['header'] = isset($v->getUserHeader->avatar) ? $v->getUserHeader->avatar : '';
            $list[$k]['type'] = json_decode($list[$k]['type']);
            $list[$k]['type'] = $cardType->getCardTypeById( $list[$k]['type']);
            $list[$k]['list_img'] = !empty($v['list_img']) ? json_decode($v['list_img']) : "";
            unset($list[$k]['getUserHeader']);
            $list[$k]['like'] = $like->isLike($list[$k]['id'],$uid);
        }
        if (IS_POST) {
            return $this->success('获取成功！','',$list);
        } else {
            $this->assign('list',$list);
            //return json_encode($list);
            return $this->fetch('card/getList');
        }

    }

    /**
     * 发表帖子
     *
     * @return \think\response\Json
     */
    public function setCard()
    {
        if (IS_POST){
            $data = input('post.');
            $uid = session("userId");
            $result = array();
            $result['front_cover'] = "";
            //第四步 新增记录
            $card_model = new CardModel();
            $res1 = $card_model->addNewCard($uid, $data['type'], $data['title'], $data['content'],'',$data['img']);
            //第五步 返回结果
            if ($res1) {

                return $this->success("发帖成功");
            } else {

                return $this->error("发帖失败");
            }
        }else{
            $cardType = new CardType();
            $cType = $cardType->getTypeList() ;
            $this->assign('cardType',json_encode($cType));


            return $this->fetch('card/setCard');
        }
    }

    /**
     * 删除帖子
     *
     * @return \think\response\Json
     */
    public function deteleCard()
    {
        $id = input('id');
        $data = input('post.');
        $card_model = new CardModel();
        Db::startTrans();
        try {
            $res = $card_model->deleteCard($id);
            if (!$res) {
                throw new Exception("帖子删除失败-" . $card_model->getError());
            }
            $error_message = $this->deleteComment($id);
            if (!$error_message) {
                if ($error_message !== 0){
                    throw new Exception("评论删除时失败-" . $error_message);
                }
            }
            Db::commit();
        } catch (Exception $ex) {
            Db::rollback();
            Log::error('删除 error_msg:' . $ex->getMessage());
            return sendErrorMessage("删除失败", "60012");
        }
        $json['msg'] = "删除成功";
        return sendSuccessmessage($json);
    }

    /**
     * 保存图片
     *
     * @param $list_img
     * @param $user_id
     * @return array
     */
    private function _setListImg($list_img)
    {
        $picture_model = new Picture();
        $list = array();
        //第二步 处理上传的图片 第一版用base64 之后改为form-data
        foreach ($list_img as $key => $value) {
            $img = base64_decode($value);
            $str = '/uploads/card/' . date('Y-m-d', time());
            $temp_path = ROOT_PATH . '/public/' . $str;
            if (!is_dir($temp_path)) {
                //不存在则新建
                createDir($temp_path);
            }

            $temp_str = sha1(time() . $key );
            //原图
            $str1 = "/" . $temp_str . ".png";
            //缩略图
            $str2 = "/" . $temp_str . "_thumb.png";
            $path = $temp_path . $str1; //真实地址
            $res = file_put_contents($path, $img);//返回的是字节数
            if ($res) {
                $database_path = $str . $str1;
                if (file_exists($path) && !file_exists($temp_path . $str2)) {
                    reduce_pic($path, $temp_path . $str2);
                }
                if (empty($list)) {
                    $list['front_cover'] = $database_path;
                } else {
                    $list['data'][] = $database_path;
                }
            } else {
                unlink($path);
            }
        }
        return $list;
    }

    /**
     * 删除评论
     * @param  $id 贴子的id
     */
    public function deleteComment($id){

        $res = Db::name('comments')->where(['aid' => $id])->delete();
        Db::name('card')->where(['id' => $id])->setInc('view',"-$res");

        return $res ;

    }
    /**
     *删除评论
     */
    public function delete(){
        $id = input('id');
        $res = Db::name("comments")->where(['id' => $id])->delete();
        Db::name('card')->where(['id' => $id])->setInc('view',-1);
        if ($res){

            return $this->success();
        }else{

            return $this->error();
        }
    }






}