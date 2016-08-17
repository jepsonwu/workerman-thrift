<?php
namespace Application\Lib;

use Application\Exceptions\CommonException;
use Noodlehaus\Config;
use Predis\Client;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Jenssegers\Mongodb\Connection;

/**
 *
 * 工厂方法 主要功能如下:
 *
 * 1.维护大部分连接池
 *   避免内存溢出
 *   重连断开连接
 *
 * Created by PhpStorm.
 * User: jepson
 * Date: 16/7/27
 * Time: 上午10:50
 */
class Factory
{
    /**
     * db 连接池
     * @var null
     */
    public static $_db_pool = null;

    /**
     * redis 连接池
     * @var null
     */
    public static $_redis_pool = null;

    /**
     * logger对象池
     * @var null
     */
    public static $_log_pool = null;

    /**
     *mongo连接池
     * @var null
     */
    public static $_mongo_pool = null;

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

    /**
     * 获取上下文对象
     * @return Context|null
     */
    public static function context()
    {
        static $context = null;

        if (is_null($context)) {
            $context = Context::getInstance();
            $context->setProtocol(self::config("logic_protocol", "json"));
        }

        return $context;
    }

    /**
     * 设置\获取\刷新配置
     * @param null $name
     * @param null $value
     * @param string $default
     * @param bool $force
     * @return mixed|Config|null
     */
    public static function config($name = null, $default = '', $value = null, $force = false)
    {
        static $config = null;

        if (is_null($config) || $force) {
            $conf_files = glob(APPLICATION_PATH . "Conf/*_" . APPLICATION_ENV . ".ini");
            $config = new Config($conf_files ? $conf_files : array());
        }

        if (!is_null($name)) {
            if (is_null($value)) {
                return $config->get($name, $default);
            } else {
                $config->set($name, $value);
            }
        }

        return $config;
    }

    /**
     * 获取logger连接
     * @param $name |logger名称
     * @param $file
     * @param string $path
     * @return array
     */
    public static function log($name, $file, $path = '')
    {
        $hash = md5($name . $file . $path);

        if (!isset(self::$_log_pool[$hash])) {
            self::$_log_pool[$hash] = new Logger($name);
            $path && $path .= "/";
            self::$_log_pool[$hash]->pushHandler(new StreamHandler(APPLICATION_PATH . "Storage/" . self::config('log_path', 'logs/') . "{$path}{$file}", Logger::WARNING));
        }

        return array($hash, self::$_log_pool[$hash]);
    }

    /**
     * 清除Logger连接池或连接
     * @param null $hash
     */
    public static function logPoolClear($hash = null)
    {
        if (is_null($hash)) {
            self::$_log_pool = null;
        } else {
            unset(self::$_log_pool[$hash]);
        }
    }

    /**
     * 获取DB链接 todo 走连接池 异步|同步  中间件或者swoole实现
     * @param array $param
     * @return mixed
     */
    public static function db($param = array())
    {
        empty($param) && $param = self::config("mysql");
        $hash = md5(serialize($param));

        if (!isset(self::$_db_pool[$hash]) || is_null(@self::$_db_pool[$hash]->getMysqli()->ping())) {//todo 根据qurey返回error_code判断是否需要重连 效率更高
            self::$_db_pool[$hash] = new Db($param);
        }

        return array($hash, self::$_db_pool[$hash]);
    }

    /**
     * 清除DB链接池
     * @param null $hash
     */
    public static function dbPoolClear($hash = null)
    {
        if (is_null($hash)) {
            self::$_db_pool = null;
        } else {
            unset(self::$_db_pool[$hash]);
        }
    }

    /**
     * 获取redis连接
     * @param array $param
     * @return mixed
     */
    public static function redis($param = array())
    {
        empty($param) && $param = self::config("redis");
        $hash = md5(serialize($param));

        if (!isset(self::$_redis_pool[$hash]) || self::$_redis_pool[$hash]->isConnected()) {//todo 这里是反的
            self::$_redis_pool[$hash] = new Client($param);
        }

        return array($hash, self::$_redis_pool[$hash]);
    }

    /**
     * 清除redis连接池或者连接
     * @param null $hash
     */
    public static function redisPoolClear($hash = null)
    {
        if (is_null($hash)) {
            self::$_redis_pool = null;
        } else {
            isset(self::$_redis_pool[$hash]) && self::$_redis_pool[$hash]->quit();
            unset(self::$_redis_pool[$hash]);
        }
    }


    /**
     * 获取mongo连接 todo something wrong
     * @param array $param
     * @return array
     */
    public static function mongo($param = array())
    {
        empty($param) && $param = self::config('mongo');
        $hash = md5(serialize($param));

        if (!isset(self::$_mongo_pool[$hash])) {
            self::$_mongo_pool[$hash] = new Connection($param);
        }

        return array($hash, self::$_mongo_pool[$hash]);
    }

    /**
     * 清除mongo连接
     * @param null $hash
     */
    public static function mongoPoolClear($hash = null)
    {
        if (is_null($hash)) {
            self::$_mongo_pool = null;
        } else {
            unset(self::$_mongo_pool[$hash]);
        }
    }

    /**
     * 异常处理
     * @param $e
     * @return mixed
     */
    public static function exceptionHandler($e)
    {
        if ($e instanceof CommonException) {
            $code = $e->getCode();
            $message = $e->getMessage();
        } else {//根据实际情况处理异常
            //todo 异步
            list($hash, $logger) = self::log("exception", "exception.log");
            $logger->error('[' . $e->getCode() . ']' . $e->getMessage());

            $code = CommonException::SYSTEM_EXCEPTION;
            $message = "系统异常!";
        }

        return self::context()->failedReturn($code, $message);
    }
}