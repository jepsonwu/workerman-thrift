<?php
namespace Application\Services;

use Application\Exceptions\CommonException;
use Application\Exceptions\SmsException;
use Application\Keys\RedisKey;
use Application\Lib\Factory;
use Application\Lib\Helper;
use Respect\Validation\Validator;

/**
 * 短信服务
 * Created by PhpStorm.
 * User: jepson
 * Date: 16/8/15
 * Time: 下午2:33
 */
class SmsService extends CommonService
{
    /**
     * 验证码过期时间 10分钟
     * @var int
     */
    protected $_code_timeout = 600;

    /**
     * 短信IP频率限制 同一IP一小时上限40
     * @var int
     */
    protected $_ip_frequency_total = 40;

    protected $_ip_frequency_time = 3600;

    /**
     * 短信分钟限制 同一个号码一分钟上限1条
     * @var int
     */
    protected $_min_frequency_total = 1;

    protected $_min_frequency_time = 60;

    /**
     * 短信时间限制  同一个号码一天上限5条
     * @var int
     */
    protected $_day_frequency_total = 5;

    protected $_day_frequency_time = 86400;

    /**
     * 发送验证码
     * @param $mobile
     * @param $ip
     * @return bool
     * @throws CommonException
     * @throws SmsException
     */
    public function sendCaptcha($mobile, $ip)
    {
        return $this->captcha($mobile, $ip);
    }

    /**
     * 发送语音验证码
     * @param $mobile
     * @param $ip
     * @return bool
     */
    public function sendVoiceCaptcha($mobile, $ip)
    {
        return $this->captcha($mobile, $ip, 'voice');
    }

    /**
     * 验证码
     * @param $mobile
     * @param $ip
     * @param string $type
     * @return bool
     * @throws CommonException
     * @throws SmsException
     */
    protected function captcha($mobile, $ip, $type = '')
    {
        switch ($type) {
            case "voice":
                $func = "sendVoice";
                $config = "sms_voice.code_template";
                break;
            default:
                $func = "send";
                $config = "sms.code_template";
                break;
        }

        $this->validaterMobile($mobile);

        $this->frequencyLimitation($mobile, $ip);

        $captcha = $this->createCaptcha();
        $this->$func($mobile, sprintf(Factory::config($config), $captcha));

        $this->setFrequency($mobile, $ip);

        list($hash, $redis) = Factory::redis();
        $redis->setex(RedisKey::SMS_CAPTCHA . $mobile, $this->_code_timeout, $captcha);

        return array("captcha" => $captcha);
    }

    /**
     * 校验验证码
     * @param $mobile
     * @param $captcha
     * @return bool
     * @throws CommonException
     * @throws SmsException
     */
    public function verifyCaptcha($mobile, $captcha)
    {
        $this->validaterMobile($mobile);

        list($hash, $redis) = Factory::redis();
        $key = RedisKey::SMS_CAPTCHA . $mobile;
        $verify_captcha = $redis->get($key);

        if (is_null($verify_captcha) || $verify_captcha != $captcha) {
            throw new SmsException("Verify captcha failed!", SmsException::VERIFY_CAPTCHA_FAILED);
        }

        $redis->del($key);

        return true;
    }

    /**
     * 生成验证码
     * @return mixed
     */
    protected function createCaptcha()
    {
        return rand('100000', '999999');
    }

    /**
     * 发送短信
     * @param $mobile
     * @param $msg
     * @param $ip
     * @return bool
     * @throws CommonException
     * @throws SmsException
     */
    public function sendMsg($mobile, $msg, $ip)
    {
        return $this->msg($mobile, $msg, $ip);
    }

    /**
     * 发送语音
     * @param $mobile
     * @param $msg
     * @param $ip
     * @return bool
     */
    public function sendVoiceMsg($mobile, $msg, $ip)
    {
        return $this->msg($mobile, $msg, $ip, 'voice');
    }

    /**
     * 短信
     * @param $mobile
     * @param $msg
     * @param $ip
     * @param string $type
     * @return bool
     * @throws CommonException
     * @throws SmsException
     */
    protected function msg($mobile, $msg, $ip, $type = '')
    {
        switch ($type) {
            case "voice":
                $func = "sendVoice";
                break;
            default:
                $func = "send";
                break;
        }

        $this->validaterMobile($mobile);

        $this->frequencyLimitation($mobile, $ip);

        $this->$func($mobile, $msg);

        $this->setFrequency($mobile, $ip);

        return true;
    }

    /**
     * 短信通道
     * @param $mobile
     * @param $msg
     * @return mixed
     * @throws SmsException
     * @throws \Exception
     */
    protected function send($mobile, $msg)
    {
        $config = Factory::config('sms');
        $post_data = "account=" . urlencode(iconv('GB2312', 'GB2312', $config['account']))
            . "&pswd=" . urlencode(iconv('GB2312', 'GB2312', $config['pswd']))
            . "&mobile={$mobile}&needstatus=false"
            . "&msg=" . mb_convert_encoding($msg, 'UTF-8', 'auto');

        $result = Helper::curl($config['url'], true, $post_data);
        $result = explode(',', $result);

        if ($result[1] != 0) {
            //todo log
            throw new SmsException("Send message failed!", SmsException::SEND_MSG_FAILED);
        }

        return $result[0];
    }

    /**
     * 语音通道
     * @param $mobile
     * @param $msg
     * @return mixed
     * @throws SmsException
     * @throws \Exception
     */
    protected function sendVoice($mobile, $msg)
    {
        $result = Helper::curl(sprintf(Factory::config("sms_voice.url"), $mobile, urlencode($msg)));
        $result = explode(',', $result);

        if ($result[1] != 0) {
            //todo log
            throw new SmsException("Send voice message failed!", SmsException::SEND_VOICE_MSG_FAILED);
        }

        return $result[0];
    }

    /**
     * 验证手机号
     * @param $mobile
     * @return int
     * @throws CommonException
     */
    protected function validaterMobile($mobile)
    {
        if (!Validator::mobile()->validate($mobile)) {
            throw new CommonException("Invalid mobile!", CommonException::INVALID_MOBILE);
        }
    }

    /**
     * 频率限制
     * @param $mobile
     * @param $ip
     * @throws SmsException
     */
    protected function frequencyLimitation($mobile, $ip)
    {
        list($hash, $redis) = Factory::redis();

        //同一IP 一小时允许发送四十条
        $total = $redis->get(RedisKey::SMS_FREQUENCY_LIMITATION . $ip);
        if ((int)$total >= $this->_ip_frequency_total)
            throw new SmsException("Send message frequent!", SmsException::SEND_MSG_FREQUENT);

        //同一号码 一分钟上限一条
        $total = $redis->get(RedisKey::SMS_FREQUENCY_LIMITATION . $mobile);
        if ((int)$total >= $this->_min_frequency_total) {
            throw new SmsException("Send message frequent!", SmsException::SEND_MSG_FREQUENT);
        }

        //同一号码 一天上限五条
        $total = $redis->get(RedisKey::SMS_DAY_FREQUENCY_LIMITATION . $mobile);
        if ((int)$total >= $this->_day_frequency_total) {
            throw new SmsException("Send message frequent!", SmsException::SEND_MSG_FREQUENT);
        }
    }

    /**
     * 设置频率缓存
     * @param $mobile
     * @param $ip
     */
    protected function setFrequency($mobile, $ip)
    {
        list($hash, $redis) = Factory::redis();

        //ip frequenty
        $key = RedisKey::SMS_FREQUENCY_LIMITATION . $ip;
        $total = $redis->incr($key);
        $total == 1 && $redis->expire($key, $this->_ip_frequency_time);

        //min frequenty
        $key = RedisKey::SMS_FREQUENCY_LIMITATION . $mobile;
        $total = $redis->incr($key);
        $total == 1 && $redis->expire($key, $this->_min_frequency_time);

        //day frequenty
        $key = RedisKey::SMS_DAY_FREQUENCY_LIMITATION . $mobile;
        $total = $redis->incr($key);
        $total == 1 && $redis->expire($key, $this->_day_frequency_time);
    }
}