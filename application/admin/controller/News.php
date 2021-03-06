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
use app\index\model\Collect;
use think\Loader;
use wechat\TPWechat;
use think\Db;
use app\index\model\Comment;
use app\common\behavior\Service;

class News extends Admin
{
    /*新闻通告首页面(园区管理员)   */
    public function index()
    {
        $type = input('type');
        $parkid = session('user_auth')['park_id'];
        $map = ['status' => 1, 'type' => ['<=', 3], 'park_id' => $parkid];
        if ($type) {
            $map['type'] = $type;
        }
        $search = input('search');
        if ($search != '') {
            $map['title'] = ['like', '%' . $search . '%'];
        }
        $list = NewsModel::where($map)->order('status asc ,create_time desc')->paginate(12,false,['query' => request()->param()]);
        int_to_string($list, $map = array('status' => array(0 => '待审核', 1 => '发布', 2 => '审核不通过')));

        $this->assign('list', $list);
        $this->assign('checkType', $type);
        return $this->fetch();
    }
    /*新闻通告审核列表(园区管理员)   */
    public function check()
    {
        if (IS_POST) {
            $data = input('');
            $new = NewsModel::get($data['id']);
            //审核通过
            if ($data['type'] == 1) {
                $new['status'] = 1;
                $new->save();
                return $this->success("审核成功");
            } //审核不通过
            elseif ($data['type'] == 2) {
                $new['status'] = 2;
                $new->save();
                return $this->success("审核成功");


            } else {
                return $this->error('参数缺失', '', '');
            }
        } else {
            $type = input('type');
            $parkid = session('user_auth')['park_id'];
            $map = ['status' => ['in', [0,2]], 'type' => ['<=', 3], 'park_id' => $parkid];
            if ($type) {
                $map['type'] = $type;
            }
            $search = input('search');
            if ($search != '') {
                $map['title'] = ['like', '%' . $search . '%'];
            }
            $list = NewsModel::where($map)->order('status asc,id desc')->paginate(12,false,['query' => request()->param()]);
            int_to_string($list, $map = array('status' => array(0 => '待审核', 1 => '审核成功', 2 => '审核不通过')));
            $this->assign('list', $list);
            $this->assign('checkType', $type);

            return $this->fetch();
        }
    }
    /*新闻通告首页面(园区操作员)   */
    public function index2()
    {
        $type = input('type');
        $parkid = session('user_auth')['park_id'];
        $map = ['status' => ['>=', 0], 'type' => ['<=', 3], 'park_id' => $parkid];
        if ($type) {
            $map['type'] = $type;
        }
        $search = input('search');
        if ($search != '') {
            $map['title'] = ['like', '%' . $search . '%'];
        }
        $list = NewsModel::where($map)->order('status asc ,create_time desc')->paginate(12,false,['query' => request()->param()]);
        int_to_string($list, $map = array('status' => array(0 => '待审核', 1 => '发布', 2 => '审核不通过')));

        $this->assign('list', $list);
        $this->assign('checkType', $type);

        return $this->fetch();
    }

   public  function  checkdetail(){

       $news = NewsModel::where('id', 'eq', input('id'))->find();
       $this->assign('news', $news);
       return $this->fetch();

   }



    /*新闻通告，政策法规的添加及修改*/
    public function add()
    {
        $parkid = session('user_auth')['park_id'];
        $pageType = input("page_type");
        if (IS_POST) {
            $pageType = input("page_type");
            $news = new NewsModel();
            if (input('id')) {
                //$_POST['status'] = 1;
                $_POST['park_id'] = $parkid;
                $result = $news->validate(true)->save($_POST, ['id' => input('id')]);
                /* //修改新闻时推送
                 if ($_POST['is_send'] == 1 && $result) {
                     $this->send(input('id'));
                 }*/
                $type = $_POST['type'];
                /*当修改新闻类型时候修改已收藏的新闻的type值*/
                $collect = new Collect();
                $re = $collect->where('target_id', input('id'))->find();
                if ($re) {
                    $collect->where('target_id', input('id'))->update(['type' => $type]);
                }
            } else {
                $_POST['park_id'] = $parkid;
                unset($_POST['id']);
                $result = $news->validate(true)->save($_POST);
                $inputid = Db::name('news')->getLastInsID();




            }

            if ($result) {

                //添加新闻时推送给审核人
                $this-> sendCheck($inputid,input('type'));
                /*// 新闻列表限制banner只有3个
                if($_POST['type'] == 1){
                    $newsMap = [
                        'is_banner' => 1,
                        'type' => 1
                    ];

                    $last = NewsModel::where($newsMap)->limit(2)->order('update_time desc')->select();
                    NewsModel::where($newsMap)->where('update_time', '<', $last[count($last)-1]['update_time'])->update(['is_banner' => 0]);
                }else{

                }*/
                if ($pageType) {

                    return $this->success('操作成功', url('News/policyLaw'));
                } else {

                    return $this->success('操作成功', url('News/index'));
                }


            } elseif ($result === 0) {
                return $this->error('没有更新内容');
            } else {
                return $this->error($news->getError());
            }
        } else {
            $news = NewsModel::where('id', 'eq', input('id'))->find();
            $this->assign('news', $news);

            /*$sendTargetName = '所有人';
            if ($news['send_type'] == 1) {
                $target = WechatDepartment::get($news['send_target']);
                $sendTargetName = $target['name'];
            }
            if ($news['send_type'] == 2) {
                $target = WechatTag::where('tagid', $news['send_target'])->find();
                $sendTargetName = $target['tagname'];
            }*/
            $this->assign('pageType', $pageType);
            ///$this->assign('sendTargetName', $sendTargetName);

            return $this->fetch();
        }
    }


    /*删除新闻*/
    public function moveToTrash()
    {
        $pageType = input('page_type');
        $ids = input('ids/a');
        $result = NewsModel::where('id', 'in', $ids)->update(['status' => -1]);

        if ($result) {
            if ($pageType) {

                return $this->success('删除成功', url('News/policylaw'));
            } else {

                return $this->success('删除成功', url('News/index'));
            }
        } elseif (!$result) {

            return $this->error('删除失败');
        }
    }


    public function sendCheck($id,$type)
    {
        $parkid = session('user_auth')['park_id'];
        $user =new WechatUser();
        $service = new Service();
        //招商人员
        $user = $user->where(['tagid'=>1,'park_id'=>$parkid])->select();
        $touser="";
        foreach ($user as $value){
            $touser .='|'.$value['userid'];
        }
        //1新闻速递、2园区通告、3好文分享
        $type_name="未知";
        switch ($type){
            case 1:$type_name="新闻动态";break;
            case 2:$type_name="政策通告";break;
            case 3:$type_name="好文分享";break;
        }
        $message = [
            "title" => "新闻审核通知",
            "description" => "操作员".session('user_auth')['username']."\n您有新的".$type_name."需要审核，点击查看详情",
            "url" => 'http://' . $_SERVER['HTTP_HOST'] . '/index/personal/newsCheck/id/' . $id
        ];

        $res = $service->sendPersonalMessage($message,$touser);
        if ($res['errcode'] == 0) {
            return true;
        } else {

            return false;
        }


    }

    /*推送*/
    public function send($id)
    {
        $parkid = session('user_auth')['park_id'];
        $news = NewsModel::get(input('id'));
        if ($id) {
            $news = NewsModel::get($id);
        }
        if ($news) {
            Loader::import('wechat\TPWechat', EXTEND_PATH);
            $weObj = new TPWechat(config('news'));

            $des = msubstr(str_replace('&nbsp;', '', strip_tags($news['content'])), 0, 36);
            $data = [
                'safe' => 0,
                'msgtype' => 'news',
                'agentid' => '1000005',
                'news' => [
                    'articles' => [[
                        'title' => $news['title'],
                        'description' => $des,
                        'url' => 'http://xk.0519ztnet.com/index/news/detail/id/' . input('id'),
                        'picurl' => empty($news['front_cover'])
                            ? 'http://xk.0519ztnet.com/index/images/news/news.jpg'
                            : 'http://xk.0519ztnet.com' . $news['front_cover']
                    ]]
                ]
            ];
            $userId = '';
            //新闻只推送给该园区的用户
            $userList = WechatUser::where('park_id',$parkid)->select();
            foreach ($userList as $user) {
                $userId .= $user['userid'] . '|';
            }
            $data['touser'] = rtrim($userId, "|");
            /*switch ($news['send_type']) {
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
            }*/

            $result = $weObj->sendMessage($data);
//            var_dump($result);
//            var_dump($weObj->errCode.'|'.$weObj->errMsg);
            if ($result['errcode'] == 0) {
                NewsModel::where('id', input('id'))->update(['is_send' => 1]);
                return $this->success('推送成功', 'News/index');
            } else {
                return $this->error('推送失败');
            }
        } else {
            $this->error('参数错误');
        }
    }

    /*获取部门*/
    public function getDepartment()
    {
        Loader::import('wechat\TPWechat', EXTEND_PATH);
        $weObj = new TPWechat(config('wechat'));
        $department = $weObj->getDepartment();

        return json($department);
    }

    /*获取部门标签*/
    public function getTag()
    {
        Loader::import('wechat\TPWechat', EXTEND_PATH);
        $weObj = new TPWechat(config('wechat'));
        $tag = $weObj->getTagList();

        return json($tag);
    }

    /*政策法规*/
    public function policyLaw()
    {
        $parkid = session('user_auth')['park_id'];
        $map = ['status' => 1, 'type' => ['>', 3], 'park_id' => $parkid];
        $search = input('search');
        if ($search != '') {
            $map['title'] = ['like', '%' . $search . '%'];
        }
        $list = NewsModel::where($map)->order('id desc')->paginate(12,false,['query' => request()->param()]);
        $this->assign('list', $list);

        return $this->fetch();

    }

    /**
     * 园区通告，好文分享显示评论
     */
    public function showcomment()
    {
        $posts = new Comment();
        // 评论列表
        $map = [
            'target_id' => input('id'),
            'status' => array('neq', -1),
        ];
        $comments = Comment::where($map)->order('id desc')->paginate(12,false,['query' => request()->param()]);
        foreach ($comments as $value) {
            $userinfo = WechatUser::where('userid', $value['user_id'])->field('header,avatar')->find();
        }
        //$count = CommunicateComment::where($map)->count();
        //echo json_encode($post);
        //echo json_encode($comments);
        $this->assign('list', $comments);

        return $this->fetch();
    }

    //逻辑删除评论
    public function deleteComment()
    {
        $ids = input('ids/a');

        $result = Comment::where('id', 'in', $ids)->update(['status' => -1]);
        if ($result) {
            return $this->success('删除成功');
        } elseif (!$result) {
            return $this->error('删除失败');
        }
    }


}