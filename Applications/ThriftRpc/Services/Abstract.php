<?php
namespace Services;

use Application\Lib\Factory;
use \ReflectionClass;
use \ReflectionMethod;

/**
 * thrift service abstract
 * Created by PhpStorm.
 * User: jepson
 * Date: 16/8/22
 * Time: 下午4:59
 */
abstract class AbstractService
{
    protected $_service_name = null;

    protected $_service_reflection = null;

    protected $_service_instance = null;

    public function __construct()
    {
        //检查是否实现接口
        $class = get_called_class();
        $class = substr($class, strrpos($class, "\\") + 1, -7);

        $this->_service_name = "\\Application\\Services\\{$class}\\{$class}Service";
        $this->_service_reflection = new ReflectionClass($this->_service_name);

        $this->_service_instance = $this->_service_reflection->newInstance();
    }

    public function __call($name, $arguments)
    {
        try {
            if (!$this->_service_reflection->hasMethod($name)) {
                throw new \Exception("Method {$name} not found");
            }

            $reflectionMethod = new ReflectionMethod($this->_service_name, $name);
            $return = $reflectionMethod->invokeArgs($this->_service_instance, $arguments);
        } catch (\Exception $e) {
            return Factory::exceptionHandler($e);
        }

        //return data what using json protocol
        return Factory::context()->successReturn($return);
    }
}