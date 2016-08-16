<?php
namespace Services\Sms;

use Application\Lib\Factory;
use Application\Services\SmsService;

/**
 * 短信服务
 * Created by PhpStorm.
 * User: jepson
 * Date: 16/8/15
 * Time: 下午2:30
 */
class SmsHandler implements SmsIf
{
    public function sendMsg($mobile, $msg)
    {
        try {
            $smsService = new SmsService();
            $return = $smsService->sendMsg($mobile, $msg);
        } catch (\Exception $e) {
            return Factory::exceptionHandler($e);
        }

        return Factory::context()->successReturn($return);
    }

    public function sendVoiceMsg($mobile, $msg)
    {
        try {
            $smsService = new SmsService();
            $return = $smsService->sendVoiceMsg($mobile, $msg);
        } catch (\Exception $e) {
            return Factory::exceptionHandler($e);
        }

        return Factory::context()->successReturn($return);
    }
}