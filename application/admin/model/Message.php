<?php
/**
 * Created by PhpStorm.
 * User: 虚空之翼
 * Date: 16/7/5
 * Time: 上午10:15
 */

namespace app\admin\model;

use think\Model;

class Message extends Model
{
    protected $insert = [
        'create_time' => NOW_TIME,
        'send_time' => NOW_TIME,    // todo 定时发送
        'status'=>1,
        'receive_id' => 0,
        'send_id' => 0
    ];

    public static function send($title='', $content='', $sendId=0, $receiveId=0) {
        $data = [
            'title' => $title,
            'content' => $content,
            'send_id' => $sendId,
            'receive_id' => $receiveId
        ];
        return self::create($data);
    }

    public static function signedSendToAnchor($receiveId, $projectName) {
        $content = '您好！您申请的'.$projectName.'专案，已通过报名并签约，请登录后台查看。制作加油！期待您的成片哦~';
        $data = [
            'title' => '申请签约',
            'content' => $content,
            'send_id' => 0, // 系统发送
            'receive_id' => $receiveId,
            'type' => 3
        ];
        return self::create($data);
    }

    public static function videoCheckSendToAnchor($receiveId) {
        $content = '您好！您提交的影片已通过审核，请记得在约定日期发布哦~期待公映呢~';
        $data = [
            'title' => '影片审核',
            'content' => $content,
            'send_id' => 0, // 系统发送
            'receive_id' => $receiveId,
            'type' => 3
        ];
        return self::create($data);
    }

    public static function videoCommentSendToAnchor($receiveId) {
        $content = '您好！您提交的影片审核失败，请查看详情~';
        $data = [
            'title' => '影片审核',
            'content' => $content,
            'send_id' => 0, // 系统发送
            'receive_id' => $receiveId,
            'type' => 3
        ];
        return self::create($data);
    }

    public static function projectCheckSendToCompany($receiveId, $projectName) {
        $content = "您好！您申请的专案[".$projectName."]已通过审核，更多信息请登录后台查看，谢谢！";
        $data = [
            'title' => '专案审核',
            'content' => $content,
            'send_id' => 0, // 系统发送
            'receive_id' => $receiveId,
            'type' => 2
        ];
        return self::create($data);
    }

    public static function projectApplySendToCompany($receiveId, $projectName){
        $content = '您好！你的专案['.$projectName.']有新的申请人，请登录后台查看，谢谢！';
        $data = [
            'title' => '专案申请',
            'content' => $content,
            'send_id' => 0, // 系统发送
            'receive_id' => $receiveId,
            'type' => 2
        ];
        return self::create($data);
    }

    public static function signedSendToCompany($receiveId, $projectName, $anchorsName) {
        $content = '您好！您的专案['.$projectName.']已与'.$anchorsName.'UP主达成合作意向并签约，请耐心等待UP主完成制作，谢谢！';
        $data = [
            'title' => '专案签约',
            'content' => $content,
            'send_id' => 0, // 系统发送
            'receive_id' => $receiveId,
            'type' => 2
        ];
        return self::create($data);
    }

    public static function newVideoSendToCompany($receiveId, $projectName) {
        $content = '您好！你的专案['.$projectName.']已有完成的影片提交，请记得查看。如需修改，请在相应页面填写修改要求并及时与我方工作人员沟通，谢谢！';
        $data = [
            'title' => '新影片提交',
            'content' => $content,
            'send_id' => 0, // 系统发送
            'receive_id' => $receiveId,
            'type' => 2
        ];
        return self::create($data);
    }

    public static function videoPublishSendToCompany($receiveId, $projectName, $url) {
        $content = '您好！您专案['.$projectName.']的影片已在优酷公开发布，链接:'.$url.'，请记得查看。';
        $data = [
            'title' => '视频发布',
            'content' => $content,
            'send_id' => 0, // 系统发送
            'receive_id' => $receiveId,
            'type' => 2
        ];
        return self::create($data);
    }


}