<?php
namespace Application\Services;

use Application\Exceptions\CommonException;
use Application\Exceptions\SmsException;
use Application\Keys\RedisKey;
use Application\Lib\Factory;
use Application\Lib\Helper;

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
     * 发送短信
     * @param $mobile
     * @param $msg
     * @return bool
     * @throws SmsException
     * @throws \Exception
     */
    public function sendMsg($mobile, $msg)
    {
        //支持发多条
        $mobile_arr = explode(",", $mobile);
        foreach ($mobile_arr as &$mobile) {
            $mobile = $this->validaterMobile($mobile);
            $this->frequencyLimitation($mobile);
        }
        $mobile = implode(',', $mobile_arr);

        $config = Factory::config('sms');
        $post_data = "account=" . urlencode(iconv('GB2312', 'GB2312', $config['account']))
            . "&pswd=" . urlencode(iconv('GB2312', 'GB2312', $config['pswd']))
            . "&mobile={$mobile}&needstatus=false"
            . "&msg=" . mb_convert_encoding($msg, 'UTF-8', 'auto');

        $result = Helper::curl($config['url'], true, $post_data);
        $result = explode(',', $result);

        if ($result[1] != 0) {
            //todo log
            throw new SmsException("发送短信失败!", SmsException::SEND_MSG_FAILED);
        }

        foreach ($mobile_arr as $val) {
            $this->setFrequency($val);
        }
        //todo log
        return true;
    }

    /**
     * 发送语音
     * @param $mobile
     * @param $msg
     * @return bool
     * @throws CommonException
     * @throws SmsException
     * @throws \Exception
     */
    public function sendVoiceMsg($mobile, $msg)
    {
        $mobile = $this->validaterMobile($mobile);
        $this->frequencyLimitation($mobile);

        $result = Helper::curl(sprintf(Factory::config("sms_voice.url"), $mobile, urlencode($msg)));
        $result = explode(',', $result);

        if ($result[1] != 0) {
            //todo log
            throw new SmsException("发送语音失败!", SmsException::SEND_VOICE_MSG_FAILED);
        }

        $this->setFrequency($mobile);
        return true;
    }

    /**
     * 验证手机号
     * @param $mobile
     * @return int
     * @throws CommonException
     */
    protected function validaterMobile($mobile)
    {
        $mobile = (int)$mobile;
        if (strlen($mobile) != 11) {//todo
            throw new CommonException("手机号格式不正确!", CommonException::INVALID_MOBILE);
        }

        return $mobile;
    }

    /**
     * 频率限制
     * @param $mobile
     * @throws SmsException
     */
    protected function frequencyLimitation($mobile)
    {
        list($hash, $redis) = Factory::redis();
        $key = RedisKey::SMS_FREQUENCY_LIMITATION . $mobile;

        $is_frequent = $redis->get($key);
        if (!is_null($is_frequent)) {
            throw new SmsException("一分钟内只允许发送一条!", SmsException::SEND_MSG_FREQUENT);
        }
    }

    /**
     *
     * 设置频率缓存
     * @param $mobile
     */
    protected function setFrequency($mobile)
    {
        list($hash, $redis) = Factory::redis();
        $key = RedisKey::SMS_FREQUENCY_LIMITATION . $mobile;

        $redis->setex($key, 60, 1);
    }
}