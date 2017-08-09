<?php
/**
 * Created by PhpStorm.
 * User: 虚空之翼
 * Date: 2017/5/9
 * Time: 上午9:24
 */

namespace app\admin\controller;

use app\common\model\News as NewsModel;
use app\common\model\WechatDepartment;
use app\common\model\WechatTag;
use app\common\model\WechatUser;
use think\Loader;
use wechat\TPWechat;

class News extends Admin
{
    public function index() {
        $map = ['status'=> 1,'type'=>['<=',3]];
        $search = input('search');
        if ($search != '') {
            $map['title'] = ['like','%'.$search.'%'];
        }
        $list = NewsModel::where($map)->order('id desc')->paginate();
        $this->assign('list', $list);

        return $this->fetch();
    }

    public function add() {
        $pageType =input("page_type");
        if(IS_POST) {
            $pageType =input("page_type");
            $news = new NewsModel();
            if(input('id')) {
                $_POST['status'] = 1;
                $result = $news->validate(true)->save($_POST, ['id'=>input('id')]);

            } else {

                unset($_POST['id']);
                $result = $news->validate(true)->save($_POST);
            }

            if($result) {
                // 新闻列表限制banner只有3个
                if($_POST['type'] == 1){
                    $newsMap = [
                        'is_banner' => 1,
                        'type' => 1
                    ];

                    $last = NewsModel::where($newsMap)->limit(2)->order('update_time desc')->select();
                    NewsModel::where($newsMap)->where('update_time', '<', $last[count($last)-1]['update_time'])->update(['is_banner' => 0]);
                }else{

                }
                if ($pageType){

                    return $this->success('添加成功', url('News/policyLaw'));
                }else{

                    return $this->success('添加成功', url('News/index'));
                }


            } elseif($result === 0) {
                return  $this->error('没有更新内容');
            } else {
                return  $this->error($news->getError());
            }
        } else {
            $news = NewsModel::where('id','eq',input('id'))->find();
            $this->assign('news', $news);

            $sendTargetName = '所有人';
            if ($news['send_type'] == 1) {
                $target = WechatDepartment::get($news['send_target']);
                $sendTargetName = $target['name'];
            }
            if ($news['send_type'] == 2) {
                $target = WechatTag::where('tagid', $news['send_target'])->find();
                $sendTargetName = $target['tagname'];
            }
            $this->assign('pageType',$pageType);
            $this->assign('sendTargetName', $sendTargetName);

            return $this->fetch();
        }
    }



    public function moveToTrash() {
        $pageType =input('page_type');
        $ids = input('ids/a');
        $result = NewsModel::where('id', 'in', $ids)->update(['status' => -1]);

        if($result) {
            if ( $pageType){

                return $this->success('删除成功', url('News/policylaw'));
            } else {

                return $this->success('删除成功', url('News/index'));
            }
        } elseif(!$result) {

            return $this->error('删除失败');
        }
    }

    public function send() {
        $news = NewsModel::get(input('id'));
        if ($news) {
            Loader::import('wechat\TPWechat', EXTEND_PATH);
            $weObj = new TPWechat(config('wechat'));

            $des = msubstr(str_replace('&nbsp;','',strip_tags($news['content'])), 0, 36);
            $data = [
                'safe' => 0,
                'msgtype' => 'news',
                'agentid' => '3',
                'news' => [
                    'articles' => [[
                        'title' => $news['title'],
                        'description' => $des,
                        'url' => config('web_url').'/index/news/detail/id/'.input('id'),
                        'picurl' => empty($news['front_cover'])
                            ? config('web_url').'/index/images/news/news.jpg'
                            : config('web_url').$news['front_cover']
                    ]]
                ]
            ];

            switch ($news['send_type']) {
                case 0:
                    $userId = '';
                    $userList = WechatUser::select();
                    foreach ($userList as $user) {
                        $userId .= $user['userid'].'|';
                    }
                    $data['touser'] = rtrim($userId, "|");
//                    $data['touser'] = '@all';
                    break;
                case 1:
                    $data['toparty'] = $news['send_target'];
                    break;
                case 2:
                    $data['totag'] = $news['send_target'];
                    break;
            }

            $result = $weObj->sendMessage($data);
//            var_dump($result);
//            var_dump($weObj->errCode.'|'.$weObj->errMsg);
            if ($result['errcode'] == 0 ) {
                NewsModel::where('id', input('id'))->update(['is_send' => 1]);
                return $this->success('推送成功');
            } else {
                return $this->error('推送失败');
            }
        } else {
            $this->error('参数错误');
        }
    }

    public function getDepartment() {
        Loader::import('wechat\TPWechat', EXTEND_PATH);
        $weObj = new TPWechat(config('wechat'));
        $department = $weObj->getDepartment();

        return json($department);
    }

    public function getTag() {
        Loader::import('wechat\TPWechat', EXTEND_PATH);
        $weObj = new TPWechat(config('wechat'));
        $tag = $weObj->getTagList();

        return json($tag);
    }

    public function policyLaw(){
        $map = ['status'=> 1,'type'=>['>',3]];
        $search = input('search');
        if ($search != '') {
            $map['title'] = ['like','%'.$search.'%'];
        }
        $list = NewsModel::where($map)->order('id desc')->paginate();
        $this->assign('list', $list);

        return $this->fetch();

    }


}