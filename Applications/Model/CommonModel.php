<?php
namespace Application\Model;

use Application\Lib\Factory;
use ArrayAccess;
use Doctrine\Instantiator\Exception\InvalidArgumentException;
use Application\Lib\Traits\ArrayAccessTrait;
use IteratorAggregate;

/**
 * Model抽象类
 *
 * 支持配置数据库类型 Mysql|Mongo
 * 支持配置默认表名
 * 支持配置默认连接参数KEY
 * 支持配置是否销毁连接 默认不销毁
 * 支持数组操作
 * 支持基础CURD操作和$this->method原生操作
 *
 * @method CommonModel where($whereProp, $whereValue = 'DBNULL', $operator = '=', $cond = 'AND')
 * @method CommonModel orWhere($whereProp, $whereValue = 'DBNULL', $operator = '=')
 * @method CommonModel having($havingProp, $havingValue = 'DBNULL', $operator = '=', $cond = 'AND')
 * @method CommonModel orHaving($havingProp, $havingValue = null, $operator = null)
 * @method CommonModel join($joinTable, $joinCondition, $joinType = '')
 * @method CommonModel orderBy($orderByField, $orderbyDirection = "DESC", $customFields = null)
 * @method CommonModel groupBy($groupByField)
 * @method CommonModel get($numRows = null, $columns = '*')
 * @method CommonModel getOne($columns = '*')
 * @method CommonModel getValue($column, $limit = 1)
 * @method CommonModel query($query, $numRows = null)
 * @method CommonModel insert($insertData)
 * @method CommonModel update($tableData, $numRows = null)
 * @method CommonModel delete($numRows = null)
 * Created by PhpStorm.
 * User: jepson
 * Date: 16/8/18
 * Time: 下午5:04
 */
abstract class CommonModel implements ArrayAccess, IteratorAggregate
{
    use ArrayAccessTrait;

    //默认数据库类型 mysql|mongo
    protected $_database_type = 'mysql';

    protected $_alow_database_type = ['mysql', 'mongo'];

    //配置文件KEY mysql|mysql1
    protected $_config_name = null;

    //表名 user
    protected $_tablename = null;

    //主键
    protected $_primary = null;

    //是否销毁连接 默认否
    protected $_keepalive = true;

    //当前连接
    protected $_connection = null;

    //当前连接hash
    protected $_connection_hash = null;

    public function __construct()
    {
        $this->_database_type = strtolower($this->_database_type);
        if (!in_array($this->_database_type, $this->_alow_database_type)) {
            throw new InvalidArgumentException("Invalid database type!");
        }

        //$this->createConnection();
    }

    protected function createConnection()
    {
        if (is_null($this->_config_name)) {
            throw new InvalidArgumentException("Invalid config name!");
        }

        switch ($this->_database_type) {
            case "mongo":
                list($this->_connection_hash, $this->_connection) = Factory::mongo(Factory::config($this->_config_name));
                break;
            default:
                list($this->_connection_hash, $this->_connection) = Factory::db(Factory::config($this->_config_name));
                break;
        }
    }

    /**
     * 获取表名
     * @return null
     */
    protected function getTablename()
    {
        if (is_null($this->_tablename)) {
            throw new InvalidArgumentException("Invalid tablename!");
        }

        return $this->_tablename;
    }

    /**
     * 获取主键
     * @return null
     */
    protected function getPrimary()
    {
        if (is_null($this->_primary)) {
            throw new InvalidArgumentException("Invalid primary!");
        }

        return $this->_primary;
    }

    /**
     * 通过主键查询
     * 当元素个数为一时返回字符串
     * @param $where
     * @param string $fields
     * @return CommonModel|array
     */
    public function getByPrimary($where, $fields = "*")
    {
        $result = array();

        switch ($this->_database_type) {
            case "mongo":
                break;
            default:
                $this->where($this->getPrimary(), $where);
                $result = $this->getOne($fields);
                break;
        }

        return count($result) == 1 ? current($result) : $result;
    }

    public function __call($name, $arguments)
    {
        switch ($this->_database_type) {
            case "mongo":
                break;
            default:
                if (in_array($name, array("get", "getOne", "getValue", "insert", "update", "delete"))) {
                    array_unshift($arguments, $this->getTablename());
                }

                return call_user_func_array(array($this->_connection, $name), $arguments);
        }
    }

    public function __destruct()
    {
        if (!$this->_keepalive) {
            switch ($this->_database_type) {
                case "mongo":
                    Factory::mongoPoolClear($this->_connection_hash);
                    break;
                default:
                    Factory::dbPoolClear($this->_connection_hash);
                    break;
            }
        }
    }

    public function getIterator()
    {
        return new \ArrayIterator($this);
    }
}