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
    /**
     * 初始化服务
     * @return \Application\Services\Sms\SmsService
     */
    public function testSmsServiceConstruct()
    {
        return new Application\Services\Sms\SmsService();
    }

    /**
     * 数据供给器
     * @return array
     */
    public function testSmsProvider()
    {
        return ['18258438129', '127.0.0.1'];
    }

    public function testVoiceProvider()
    {
        return ['13957404862', '127.0.0.1'];
    }

    /**
     * @depends testSmsProvider
     * @depends testVoiceProvider
     * @param $sms_data
     * @param $voice_data
     */
    public function testSmsClearRedis($sms_data, $voice_data)
    {
        $obj = \Application\Keys\RedisKey::getInstance();
        $this->assertNotEmpty($obj->delKeys($obj->keys(array(
            "sms_*{$sms_data[0]}",
            "sms_*{$sms_data[1]}",
            "sms_*{$voice_data[0]}",
            "sms_*{$voice_data[1]}"
        ))), "删除Redis键失败!");
    }

    /**
     * 测试手机号格式
     * @depends testSmsServiceConstruct
     * @expectedException \Application\Exceptions\CommonException
     * @expectedExceptionCode \Application\Exceptions\CommonException::INVALID_MOBILE
     * @param $smsService
     */
    public function testSendCaptchaInvalidMobile($smsService)
    {
        $smsService->sendCaptcha("1234", '127.0.0.1');
    }

    /**
     * 测试短信验证码
     * @depends      testSmsProvider
     * @depends      testSmsServiceConstruct
     * @param $data
     * @param $smsService
     * @return array
     */
    public function testSendCaptcha($data, $smsService)
    {
        $result = $smsService->sendCaptcha($data[0], $data[1]);
        $this->assertArrayHasKey("captcha", $result, "发送验证码失败!");

        return array($data[0], $result['captcha']);
    }

    /**
     * 重复发送
     * @depends      testSmsProvider
     * @depends      testSmsServiceConstruct
     * @expectedException \Application\Exceptions\SmsException
     * @expectedExceptionCode \Application\Exceptions\SmsException::SEND_MSG_FREQUENT
     * @param $data
     * @param $smsService
     */
    public function testSendMsgFrequent($data, $smsService)
    {
        $smsService->sendMsg($data[0], "您的验证码是1234【英语趣配音】", $data[1]);
    }

    /**
     * 测试语音验证码
     * @depends      testVoiceProvider
     * @depends      testSmsServiceConstruct
     * @param $data
     * @param $smsService
     */
    public function testSendVoiceCaptcha($data, $smsService)
    {
        $result = $smsService->sendVoiceCaptcha($data[0], $data[1]);
        $this->assertArrayHasKey("captcha", $result, "发送语音验证码失败!");
    }

    /**
     * 校验验证码
     * @depends testSendCaptcha
     * @depends testSmsServiceConstruct
     * @param $data
     * @param $smsService
     */
    public function testVerifyCaptcha($data, $smsService)
    {
        $this->assertTrue($smsService->verifyCaptcha($data[0], $data[1]), "验证码校验失败!");
    }

//    /**
//     * 发送语音
//     * @dataProvider mobileProvider
//     * @depends      testSmsServiceConstruct
//     * @param $mobile
//     * @param $smsService
//     */
//    public function testSendVoiceMsg($mobile, $smsService)
//    {
//        $this->assertTrue($smsService->sendVoiceMsg($mobile, "发送语音测试"), "发送语音失败!");
//    }
//
//    /**
//     * 发送短信
//     * @dataProvider mobileProvider
//     * @depends      testSmsServiceConstruct
//     * @param $mobile
//     * @param $smsService
//     */
//    public function testSendMsg($mobile, $smsService)
//    {
//        list($hash, $redis) = \Application\Lib\Factory::redis();
//        //设置缓存失效
//        $mobile_arr = explode(",", $mobile);
//        foreach ($mobile_arr as $val) {
//            $redis->del(\Application\Keys\RedisKey::SMS_FREQUENCY_LIMITATION . $val);
//        }
//
//        $this->assertTrue($smsService->sendMsg($mobile, "您的验证码是1234【英语趣配音】"), "发送短信失败!");
//    }
}