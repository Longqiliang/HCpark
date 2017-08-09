<?php
/**
 * Created by PhpStorm.
 * User: 虚空之翼
 * Date: 2017/5/10
 * Time: 下午1:35
 */

namespace wechat;

use think\Exception;

class Prpcrypt
{
    public $key;

    function __construct($k) {
        $this->key = base64_decode($k . "=");
    }

    /**
     * 兼容老版本php构造函数，不能在 __construct() 方法前边，否则报错
     */
    function Prpcrypt($k)
    {
        $this->key = base64_decode($k . "=");
    }

    /**
     * 对明文进行加密
     * @param string $text 需要加密的明文
     * @return string 加密后的密文
     */
    public function encrypt($text, $appid)
    {
        try {
            $key = $this->key;
            $random = $this->getRandomStr();
            $text = $this->encode($random.pack('N', strlen($text)).$text.$appId);

            $iv = substr($key, 0, 16);

            $encrypted = openssl_encrypt($text, 'aes-256-cbc', $key, OPENSSL_RAW_DATA | OPENSSL_NO_PADDING, $iv);

            return base64_encode($encrypted);
        } catch (Exception $e) {
            return array($e->getMessage(), null);
        }
    }

    /**
     * 对密文进行解密
     * @param string $encrypted 需要解密的密文
     * @return string 解密得到的明文
     */
    public function decrypt($encrypted, $appid)
    {
        try {
            $key = $this->key;
            $ciphertext = base64_decode($encrypted, true);
            $iv = substr($key, 0, 16);

            $decrypted = openssl_decrypt($ciphertext, 'aes-256-cbc', $key, OPENSSL_RAW_DATA | OPENSSL_NO_PADDING, $iv);
        } catch (Exception $e) {
            return array($e->getMessage(), null);
        }
        try {
            $result = $this->decode($decrypted);

            if (strlen($result) < 16) {
                return '';
            }

            $content = substr($result, 16, strlen($result));
            $listLen = unpack('N', substr($content, 0, 4));
            $xmlLen = $listLen[1];
            $xml = substr($content, 4, $xmlLen);
            $fromAppId = trim(substr($content, $xmlLen + 4));
        } catch (Exception $e) {
            return array($e->getMessage(), null);
        }

        if ($fromAppId != $appid)
            return array(ErrorCode::$ValidateAppidError, null);


        return array(0, $xml);
    }


    /**
     * 随机生成16位字符串
     * @return string 生成的字符串
     */
    function getRandomStr()
    {

        $str = "";
        $str_pol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($str_pol) - 1;
        for ($i = 0; $i < 16; $i++) {
            $str .= $str_pol[mt_rand(0, $max)];
        }
        return $str;
    }

    protected function getAESKey()
    {
        if (empty($this->key)) {
            return array("Configuration mission, 'aes_key' is required.", null);
        }

        if (strlen($this->key) !== 43) {
            return array("The length of 'aes_key' must be 43.", null);
        }

        return base64_decode($this->key.'=', true);
    }

    /**
     * Decode string.
     *
     * @param string $decrypted
     *
     * @return string
     */
    public function decode($decrypted)
    {
        $pad = ord(substr($decrypted, -1));

        if ($pad < 1 || $pad > 32) {
            $pad = 0;
        }

        return substr($decrypted, 0, (strlen($decrypted) - $pad));
    }

    /**
     * Encode string.
     *
     * @param string $text
     *
     * @return string
     */
    public function encode($text)
    {
        $padAmount = 32 - (strlen($text) % 32);

        $padAmount = $padAmount !== 0 ? $padAmount : 32;

        $padChr = chr($padAmount);

        $tmp = '';

        for ($index = 0; $index < $padAmount; ++$index) {
            $tmp .= $padChr;
        }

        return $text.$tmp;
    }
}