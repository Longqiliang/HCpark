<?php
/**
 * Created by PhpStorm.
 * User: shen
 * Date: 2017/8/28
 * Time: 下午2:24
 */
namespace app\admin\controller;
use app\common\model\Union as UnionModel;
use think\Loader;
use wechat\TPWechat;
use app\common\model\WechatUser;
class Union extends Admin
{
    /*公告活动首页*/
    public function index() {
        $parkid = session('user_auth')['park_id'];
        $map = ['status'=> 1,'park_id'=>$parkid];
        $search = input('search');
        if ($search != '') {
            $map['title'] = ['like','%'.$search.'%'];
        }
        $list = UnionModel::where($map)->order('create_time desc')->paginate();
        int_to_string($list,array(
            'type'=>array(1=>'通知公告',3=>'相关活动')
        ));
        $this->assign('list', $list);

        return $this->fetch();
    }
    /*通知公告，相关活动的添加及修改*/
    public function add() {
        $parkid = session('user_auth')['park_id'];

        if(IS_POST) {
            $union = new UnionModel();
            if(input('id')) {
                $_POST['status'] = 1;
                $_POST['park_id']=$parkid;
                $result = $union->validate(true)->save($_POST, ['id'=>input('id')]);

            } else {
                $_POST['park_id']=$parkid;
                unset($_POST['id']);
                $result = $union->validate(true)->save($_POST);
            }

            if ($result) {
                $this->success('添加成功', url('Union/index'));
            } elseif ($result === 0) {
                $this->error('没有更新内容');
            } else {
                $this->error($union->getError());
            }

        } else {
            $union = UnionModel::where('id','eq',input('id'))->find();
            $this->assign('res', $union);

            return $this->fetch();
        }
    }


    /*逻辑删除*/
    public function moveToTrash() {
        $ids = input('ids/a');
        $result = UnionModel::where('id', 'in', $ids)->update(['status' => -1]);

        if($result) {

                return $this->success('删除成功', url('Union/index'));

        } elseif(!$result) {

            return $this->error('删除失败');
        }
    }

    /*推送*/
    public function send() {
        $news = UnionModel::get(input('id'));
        if ($news) {
            Loader::import('wechat\TPWechat', EXTEND_PATH);
            $weObj = new TPWechat(config('news'));

            $des = msubstr(str_replace('&nbsp;','',strip_tags($news['content'])), 0, 36);
            $data = [
                'safe' => 0,
                'msgtype' => 'news',
                'agentid' => '1000005',
                'news' => [
                    'articles' => [[
                        'title' => $news['title'],
                        'description' => $des,
                        'url' => 'http://xk.0519ztnet.com/index/news/detail/id/'.input('id'),
                        'picurl' => empty($news['front_cover'])
                            ? 'http://xk.0519ztnet.com/index/images/news/news.jpg'
                            : 'http://xk.0519ztnet.com'.$news['front_cover']
                    ]]
                ]
            ];
            $userId = '';
            $userList = WechatUser::select();
            foreach ($userList as $user) {
                $userId .= $user['userid'].'|';
            }
            $data['touser'] = rtrim($userId, "|");


            $result = $weObj->sendMessage($data);

            if ($result['errcode'] == 0 ) {
                UnionModel::where('id', input('id'))->update(['is_send' => 1]);
                return $this->success('推送成功');
            } else {
                return $this->error('推送失败');
            }
        } else {
            $this->error('参数错误');
        }
    }





}