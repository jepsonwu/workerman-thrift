<?php
namespace Application\Services;


use Application\Exceptions\CommonException;
use Application\Exceptions\HelloWorldException;
use Application\Lib\Factory;

/**
 * Created by PhpStorm.
 * User: jepson
 * Date: 16/8/1
 * Time: 上午10:54
 */
class HelloWorldService extends CommonService
{
    /**
     *
     * @param $name
     * @return string
     * @throws HelloWorldException
     */
    public function sayHello($name)
    {
        if (!preg_match("/^[a-zA-Z]+$/", $name))
            throw new HelloWorldException("name failed", HelloWorldException::INVALID_ARGUMENTS);
        //get conf
//        $conf = Factory::config("timezone");

        //get instance of log
//        list($hash, $logger) = Factory::log("hello_world", "hello_world");
//        $logger->info("sayHello log");

        //usually,you need to destory the instance when nothing to do
//        Factory::logPoolClear($hash);

        //get instance of db
        list($hash, $db) = Factory::db();
        $db->where("UID", 1);
        $nickname = $db->getOne("user", "Name");

//
//        Factory::dbPoolClear($hash);
        //maybe,you need to destory the instance when nothing to do
//        Factory::dbPoolClear($hash);

        //get instance of redis
//        list($hash, $client) = Factory::redis();
//        $name = $client->get(RedisKey::HELLO_WORLD_NAME);

        //maybe,you need to destory the instance when nothing to do
//        Factory::redisPoolClear($hash);

        //get instance of mongo
//        list($hash,$mongo)=Factory::mongo();
//        var_dump($mongo->collection("test")->get());
//
//        Factory::mongoPoolClear($hash);

        //exception
        //throw new HelloWorldException("参数错误", HelloWorldException::SAY_HELLO_NAME_FAILED);

        return "Hello {$name},{$nickname['Name']}";
    }
}