<?php
namespace Services\Sms;

use Application\Lib\Factory;
use Application\Services\Sms\SmsService;
use Services\AbstractService;

/**
 * 短信服务
 * Created by PhpStorm.
 * User: jepson
 * Date: 16/8/15
 * Time: 下午2:30
 */
class SmsHandler extends AbstractService
{
    /**
     *
     * @param string $mobile
     * @param string $ip
     * @return mixed
     */
//    public function sendCaptcha($mobile, $ip)
//    {
//        try {
//            $smsService = new SmsService();
//            $return = $smsService->sendCaptcha($mobile, $ip);
//        } catch (\Exception $e) {
//            return Factory::exceptionHandler($e);
//        }
//
//        return Factory::context()->successReturn($return);
//    }

    /**
     * @param string $mobile
     * @param string $ip
     * @return mixed
     */
    public function sendVoiceCaptcha($mobile, $ip)
    {
        try {
            $smsService = new SmsService();
            $return = $smsService->sendVoiceCaptcha($mobile, $ip);
        } catch (\Exception $e) {
            return Factory::exceptionHandler($e);
        }

        return Factory::context()->successReturn($return);
    }

    /**
     *
     * @param string $mobile
     * @param int $captcha
     * @return mixed
     */
    public function verifyCaptcha($mobile, $captcha)
    {
        try {
            $smsService = new SmsService();
            $return = $smsService->verifyCaptcha($mobile, $captcha);
        } catch (\Exception $e) {
            return Factory::exceptionHandler($e);
        }

        return Factory::context()->successReturn($return);
    }

    /**
     * 发送短信
     * @param string $mobile
     * @param string $msg
     * @param string $ip
     * @return mixed
     */
    public function sendMsg($mobile, $msg, $ip)
    {
        try {
            $smsService = new SmsService();
            $return = $smsService->sendMsg($mobile, $msg, $ip);
        } catch (\Exception $e) {
            return Factory::exceptionHandler($e);
        }

        return Factory::context()->successReturn($return);
    }

    /**
     * 发送语音
     * @param string $mobile
     * @param string $msg
     * @param string $ip
     * @return mixed
     */
    public function sendVoiceMsg($mobile, $msg, $ip)
    {
        try {
            $smsService = new SmsService();
            $return = $smsService->sendVoiceMsg($mobile, $msg, $ip);
        } catch (\Exception $e) {
            return Factory::exceptionHandler($e);
        }

        return Factory::context()->successReturn($return);
    }
}