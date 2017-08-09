<?php
/**
 * Created by PhpStorm.
 * User: Aion
 * Date: 16/7/3
 * Time: 下午12:01
 */

namespace app\admin\controller;

use app\admin\model\Message as MessageModel;
use app\admin\model\MessageStatus;

class Message extends Admin
{
    public function index() {
        $list = MessageModel::where('status', 1)->order('id desc')->paginate();
        $this->assign('list', $list);

        return $this->fetch();
    }

    /**
     * 发送消息
     * @return array|mixed
     */
    public function send() {
        if(IS_POST) {
            $message = new MessageModel();
            $_POST['receive_id'] = $_POST['receive_id'] ?: 0;
            if(input('id')) {
                $_POST['status'] = 1;
                $result = $message->allowField(true)->validate(true)->save($_POST, ['id'=>input('id')]);
            } else {
                unset($_POST['id']);
                $result = $message->allowField(true)->validate(true)->save($_POST);
            }

            if($result) {
                $this->success('发送成功', url('Message/index'));
            } else {
                $this->error($message->getError());
            }
        } else {
            $message = MessageModel::get(input('id', 0));
            $this->assign('message', $message);

            return $this->fetch();
        }
    }

    /**
     * 草稿箱
     * @return array|mixed
     */
    public function draft() {
        if(IS_POST) {
            $message = new MessageModel();
            $_POST['receive_id'] = $_POST['receive_id'] ?: 0;
            $_POST['status'] = 0;
            if(input('id')) {
                $result = $message->allowField(true)->validate(true)->save($_POST, ['id'=>input('id')]);
            } else {
                unset($_POST['id']);
                $result = $message->allowField(true)->validate(true)->save($_POST);
            }
            if($result) {
                $this->success('保存成功', url('Message/draft'));
            } else {
                $this->error($message->getError());
            }
        } else {
            $list = MessageModel::where('status', 0)->paginate(12);
            $this->assign('list', $list);

            return $this->fetch();
        }
    }

    /**
     * 回收站
     * @return mixed
     */
    public function trash() {
        $list = MessageModel::where('status', -1)->paginate(12);
        $this->assign('list', $list);

        return $this->fetch();
    }

    /**
     * 消息详情
     * @return mixed
     */
    public function view() {
        $message = MessageModel::get(input('id'));
        $this->assign('message', $message);
        // 表示已经阅
        MessageStatus::isView(UID, input('id'), 1);

        $nextMessage = MessageModel::where('id', '>', input('id'))->limit(1)->find();
        $this->assign('nextMessage', $nextMessage);

        return $this->fetch();
    }

    /**
     * 移到回收站
     * @return array
     * @throws \think\Exception
     */
    public function moveToTrash() {
        $result = MessageModel::where('id', 'in', input('ids/a'))->update(['status' => -1]);
        if($result) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }

    /**
     * 批量删除消息
     * @return array
     */
    public function delete() {
        $ids = input('ids/a');
        if(empty($ids)){
            $this->error('请选择要删除的数据');
        }

        $result = MessageModel::where('id', 'in', $ids)->delete();
        if($result) {
            // 删除相关的消息标识
            MessageStatus::where('message_id', 'in', $ids)->delete();
            $this->success('删除成功', url('Message/index'));
        } else {
            $this->error('删除失败');
        }
    }

    /**
     * 收件箱
     * @return mixed
     */
    public function inbox() {
        // 标识未读的信息
        $map = [ 'type' => 1, 'member_id' => UID, 'is_view' => 1, 'is_delete' => 0 ];
        $messageStatus = MessageStatus::where($map)->column('message_id');
        // 标识已经删除的信息
        $deleteMap = [ 'type' => 1, 'member_id' => UID, 'is_delete' => 1 ];
        $deleteStatus = MessageStatus::where($deleteMap)->column('message_id');
        $deleteStatus = empty($deleteStatus) ? [0] : $deleteStatus;

        $list = MessageModel::where('status', 1)->where('type', 'in', '1')->where('receive_id', 'exp', 'in (0, '.UID.') ')
            ->where('id', 'not in', $deleteStatus)->order('id desc')->paginate();
        foreach ($list as $key => $value) {
            $value['is_view'] = in_array($value['id'], $messageStatus) ? 1 : 0;
        }
        $this->assign('list', $list);

        return $this->fetch();
    }
}