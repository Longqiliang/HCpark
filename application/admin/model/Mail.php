<?php
/**
 * Created by PhpStorm.
 * User: 虚空之翼
 * Date: 16/7/8
 * Time: 下午3:10
 */

namespace app\admin\model;

use PHPMailer;
use think\Model;
use think\Validate;

class Mail extends Model
{
    protected $insert = [
        'create_time' => NOW_TIME,
        'send_time' => NOW_TIME,    // todo 定时发送
        'status' => 1,
    ];

    /**
     * 发送邮件
     * @param $to
     * @param $subject
     * @param $body
     * @return bool
     */
    public function send($to, $subject, $body) {
        if(!Validate::is($to,'email')) {
            $this->error = '邮件地址错误';
            return false;
        }

        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->CharSet='UTF-8';
        $mail->Host = config('mail_smtp');
        $mail->SMTPAuth = true;
        $mail->Username = config('mail_username');
        $mail->Password = config('mail_password');
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;

        $mail->setFrom(config('mail_username'), '灵煽科技');
        $mail->addAddress($to);
        $mail->isHTML(true);

        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = $body; // no-html client

        if($mail->send()) {
            $this->save([
                'from' => '灵煽科技<'.config('mail_username').'>',
                'to' => $to,
                'subject' => $subject,
                'body' => $body
            ]);

            return true;
        } else {
            $this->error = $mail->ErrorInfo;
            return false;
        }
    }

    /**
     * 密码找回邮件
     * @param $email
     * @param $signed
     * @return bool
     */
    public function sendForgotPassword($email, $signed) {
        $url = url('base/reset', 'code='.$signed , true, true);

        $body = '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <link href="'.config('web_url').'/admin/css/email.css" rel="stylesheet">
        </head>
        <body>
        <div class="mail-container" style="    position: relative; width: 620px;font-size: 14px;margin: 0 auto;">
            <div class="logo" style="height: 40px;font-size: 30px;line-height: 30px;">
                <img src="'.config('web_url').'/index/images/video.png" height="100%"/>
                <span style="display: block;position: absolute;top: 2px;left: 36px;">'.config('WEB_SITE_TITLE').'</span>
            </div>
            <div class="mail-content" style="   width: 100%;border-top: 1px solid #999;border-bottom: 1px solid #999;">
                <p>感谢您注册<a href="'.config('web_url').'" style=" color: #82b95e;text-decoration: none;">'.config('WEB_SITE_TITLE').'</a></p>
                <p>请点击以下链接重置你的密码</p>
                <div class="mail-url" style="  word-break: break-all; width: 600px;background-color: #82b95e; border-radius: 2px; padding: 10px 10px;">
                    <span><a href="'.$url.'">'.$url.'</a></span>
                </div>
                <p>密码重置链接将在48小时后失效。</p>
                <p>请勿回复此邮件</p>
                <p class="font-email">--灵煽技术团队</p>
            </div>
            <p class="font-email" style="color: #999">如果你没有注册过，请忽略此邮件</p>
        </div>
        </body>
        </html>';

        return self::send($email, "密码找回", $body);
    }

    public function sendActiveUser($email, $signed) {
        $url = url('base/active', 'code='.$signed , true, true);

        $body = '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <link href="'.config('web_url').'/admin/css/email.css" rel="stylesheet">
        </head>
        <body>
        <div class="mail-container" style="    position: relative; width: 620px;font-size: 14px;margin: 0 auto;">
            <div class="logo" style="height: 40px;font-size: 30px;line-height: 30px;">
                <img src="\'.config(\'web_url\').\'/index/images/video.png" height="100%"/>
                <span style="display: block;position: absolute;top: 2px;left: 36px;">\'.config(\'WEB_SITE_TITLE\').\'</span>
            </div>
            <div class="mail-content" style="   width: 100%;border-top: 1px solid #999;border-bottom: 1px solid #999;">
                <p>感谢您注册<a href="\'.config(\'web_url\').\'" style=" color: #82b95e;text-decoration: none;">\'.config(\'WEB_SITE_TITLE\').\'</a></p>
                <p>请点击以下链接激活你的帐号</p>
                <div class="mail-url" style="  word-break: break-all; width: 600px;background-color: #82b95e; border-radius: 2px; padding: 10px 10px;">
                    <span><a href="'.$url.'">'.$url.'</a></span>
                </div>
                <p>密码激活链接将在48小时后失效。</p>
                <p>请勿回复此邮件</p>
                <p class="font-email">--灵煽技术团队</p>
            </div>
            <p class="font-email" style="color: #999">如果你没有注册过，请忽略此邮件</p>
        </div>
        </body>
        </html>';

        return self::send($email, "帐号激活", $body);
    }

}