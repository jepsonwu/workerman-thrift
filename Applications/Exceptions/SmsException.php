<?php
namespace Application\Exceptions;
/**
 * Created by PhpStorm.
 * User: jepson
 * Date: 16/8/15
 * Time: 下午2:42
 */
class SmsException extends CommonException
{
    const SEND_MSG_FAILED = 10001;//发送短信失败
    const SEND_VOICE_MSG_FAILED = 10002;//发送语音失败
    const SEND_MSG_FREQUENT = 10003;//发送短信频繁的
}