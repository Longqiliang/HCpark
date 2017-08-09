<?php
/**
 * Created by PhpStorm.
 * User: 虚空之翼
 * Date: 16/8/15
 * Time: 下午4:25
 */

namespace app\admin\model;

use com\Ucpaas;
use think\Model;
use Toplan\PhpSms\Sms as SmsLib;

class Sms extends Model
{
    protected $insert = [
        'create_time' => NOW_TIME,
        'status' => -1
    ];

    /**
     * @param $to
     * @param $templateId
     * @param $tempData
     * @return mixed|string
     * 短信验证码（模板短信），默认以65个汉字（同65个英文）为一条（可容纳字数受您应用名称占用字符影响），
     * 超过长度短信平台将会自动分割为多条发送。分割后的多条短信将按照具体占用条数计费。
     */
    protected static function send($to, $templateId, $tempData) {
        SmsLib::config([ 'Ucpaas' => config('Ucpaas') ]);
        SmsLib::scheme([ 'Ucpaas' => '1' ]);
        $templates = [ 'Ucpaas' => $templateId ];

        $result =  SmsLib::make()->to($to)->template($templates)->data($tempData)->send();
        $logs = $result['logs'][0]['result'];
        $info = object_array(json_decode($result['logs'][0]['result']['info']));
        $data = [
            'to' => $to,
            'content' => json_encode($tempData),
            'template_id' => $templateId,
            'result' => json_encode($logs['info']),
            'resp_code' => $logs['code'],
        ];
        if($logs['code'] == '0000000') {
            $data['sms_id'] =  $info['templateSMS']['smsId'];
            $data['create_date'] =  $info['templateSMS']['createDate'];
        }
        self::create($data);

        return $result;
    }

    /**
     * @param $to
     * @param $code
     * @return mixed|string
     * 发送验证码信息
     */
    public static function sendCode($to, $code) {
        $templateId = '27980';
        $tempData = [$code, 10];

        $data = [
            'mobile' => $to,
            'code' => $code,
            'active_time' => 60 * 10
        ];
        SmsCode::create($data);

        return self::send($to, $templateId, $tempData);
    }

    // 发送给厂商通知
    public static function projectCheckSendToCompany($to, $projectName) {
        $templateId = '28031';
        $tempData = [$projectName];

        return self::send($to, $templateId, $tempData);
    }

    public static function projectApplySendToCompany($to, $projectName){
        $templateId = '28036';
        $tempData = [$projectName];

        return self::send($to, $templateId, $tempData);
    }

    public static function signedSendToCompany($to, $projectName, $anchorsName) {
        $templateId = '28037';
        $tempData = [$projectName, $anchorsName];

        return self::send($to, $templateId, $tempData);
    }

    public static function newVideoSendToCompany($to, $projectName) {
        $templateId = '28038';
        $tempData = [$projectName];

        return self::send($to, $templateId, $tempData);
    }

    public static function videoPublishSendToCompany($to, $projectName) {
        $templateId = '28039';
        $tempData = [$projectName];

        return self::send($to, $templateId, $tempData);
    }

    // 发送给主播通知
    public static function signedSendToAnchor($to, $projectName) {
        $templateId = '28032';
        $tempData = [$projectName];

        return self::send($to, $templateId, $tempData);
    }

    public static function videoCheckSendToAnchor($to) {
        $templateId = '28033';
        $tempData = [];

        return self::send($to, $templateId, $tempData);
    }

}