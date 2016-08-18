<?php
namespace Application\Keys;

use Application\Lib\Factory;

/**
 *
 * 键值管理 例如redis queue键
 * Created by PhpStorm.
 * User: jepson
 * Date: 16/7/29
 * Time: 上午11:29
 */
class RedisKey
{
    //短信服务
    const SMS_FREQUENCY_LIMITATION = 'sms_frequency_limitation_';//频率限制
    const SMS_DAY_FREQUENCY_LIMITATION = 'sms_day_frequency_limitation_';//时间频率限制
    const SMS_CAPTCHA = 'sms_captcha_';//验证码

    /**
     * 连接参数
     * @var null
     */
    protected $_config = array();


    public static $_instance = null;

    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    private function __construct()
    {
    }

    public function setConfig($config)
    {
        $this->_config = $config;
    }

    protected function getRedis()
    {
        list($hash, $redis) = Factory::redis($this->_config);
        return $redis;
    }

    /**
     * 获取缓存键值
     * 模式匹配 默认取当前类所有前缀可能键值
     * @param null $pattern
     * @return array
     */
    public function keys($pattern = null)
    {
        //取所有
        if (is_null($pattern)) {
            $reflection = new \ReflectionClass($this);
            $pattern = array_map(function ($val) {
                return "{$val}*";
            }, $reflection->getConstants());
        }

        is_string($pattern) && $pattern = array($pattern);

        $keys = array();
        if (!empty($pattern)) {
            foreach ($pattern as $pat) {
                $keys = array_merge($keys, $this->getRedis()->keys($pat));
            }
        }
        
        return $keys;
    }

    /**
     * 删除key
     * @param $keys
     */
    public function delKeys($keys)
    {
        return $this->getRedis()->del($keys);
    }
}