<?php
/**
 * Created by PhpStorm.
 * User: 虚空之翼
 * Date: 16/7/13
 * Time: 上午10:24
 */

namespace app\admin\model;

use think\Model;

class MessageStatus extends Model
{
    protected $insert = [
        'is_view' => 0,
        'is_delete' => 0
    ];

    public static function isView($memberId, $messageId, $type=1) {
        $data = [
            'member_id' => $memberId,
            'message_id' => $messageId,
            'type' => $type
        ];

        $messageStatus = self::where($data)->find();
        if($messageStatus) {
            self::where($data)->update(['is_view'=>1]);
        } else {
            $data['is_view'] = 1;
            self::create($data);
        }
    }

    public static function isDelete($memberId, $messageId, $type=1) {
        $data = [
            'member_id' => $memberId,
            'message_id' => $messageId,
            'type' => $type
        ];

        $messageStatus = self::where($data)->find();
        if($messageStatus) {
            self::where($data)->update(['is_delete'=>1]);
        } else {
            $data['is_delete'] = 1;
            self::create($data);
        }
    }
}