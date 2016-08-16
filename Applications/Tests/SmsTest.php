<?php
use PHPUnit\Framework\TestCase;

/**
 * @group base_module
 * Created by PhpStorm.
 * User: jepson
 * Date: 16/8/15
 * Time: 下午3:46
 */
class SmsTest extends TestCase
{
    public function mobileProvider()
    {
        return [
            ['18258438129'],
            //multi
        ];
    }

    /**
     *
     * @return \Application\Services\SmsService
     */
    public function testSmsServiceConstruct()
    {
        return new Application\Services\SmsService();
    }

    /**
     * 测试手机号格式
     * @depends testSmsServiceConstruct
     * @expectedException \Application\Exceptions\CommonException
     * @expectedExceptionCode \Application\Exceptions\CommonException::INVALID_MOBILE
     * @param $smsService
     */
    public function testSendVoiceMsgInvalidMobile($smsService)
    {
        $smsService->sendVoiceMsg("1234", "您的验证码是12312");
    }

    /**
     * 发送语音
     * @dataProvider mobileProvider
     * @depends      testSmsServiceConstruct
     * @param $mobile
     * @param $smsService
     */
    public function testSendVoiceMsg($mobile, $smsService)
    {
        $this->assertTrue($smsService->sendVoiceMsg($mobile, "您的验证码是1234"), "发送语音失败!");
    }

    /**
     * 发送短信
     * @dataProvider mobileProvider
     * @depends      testSmsServiceConstruct
     * @param $mobile
     * @param $smsService
     */
    public function testSendMsg($mobile, $smsService)
    {
        list($hash, $redis) = \Application\Lib\Factory::redis();
        //设置缓存失效
        $mobile_arr = explode(",", $mobile);
        foreach ($mobile_arr as $val) {
            $redis->del(\Application\Keys\RedisKey::SMS_FREQUENCY_LIMITATION . $val);
        }

        $this->assertTrue($smsService->sendMsg($mobile, "您的验证码是1234【英语趣配音】"), "发送短信失败!");
    }

    /**
     * 重复发送
     * @dataProvider mobileProvider
     * @depends      testSmsServiceConstruct
     * @expectedException \Application\Exceptions\SmsException
     * @expectedExceptionCode \Application\Exceptions\SmsException::SEND_MSG_FREQUENT
     * @param $mobile
     * @param $smsService
     */
    public function testSendMsgFrequent($mobile, $smsService)
    {
        $smsService->sendMsg($mobile, "您的验证码是1234【英语趣配音】");
    }
}