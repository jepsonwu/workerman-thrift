<?php
namespace Application\Model;

use ArrayAccess;

/**
 * 连接池Model抽象类
 *
 * Created by PhpStorm.
 * User: jepson
 * Date: 16/8/19
 * Time: 上午10:28
 */
abstract class CommonPoolModel implements ArrayAccess
{

    //默认数据库类型 mysql|mongo
    protected $_database_type = 'mysql';

    protected $_alow_database_type = ['mysql', 'mongo'];

    //配置文件KEY mysql|mysql1
    protected $_config_name = null;

    //表名 user
    protected $_tablename = null;

    //当前连接
    protected $_connection = null;

    public function __construct()
    {
    }

    public function createConnection()
    {
        
    }

    public function offsetExists($offset)
    {
        return isset($this->$offset);
    }

    public function offsetGet($offset)
    {
        return $this->$offset;
    }

    public function offsetSet($offset, $value)
    {
        $this->$offset = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->$offset);
    }


    public function __call($name, $arguments)
    {
        // TODO: Implement __call() method.
    }

}