<?php
namespace Application\Lib;

use Application\Lib\Database\AsyncMysqliPool;
use Application\Lib\Database\MysqlBuildQuery;

/**
 * db 操作类
 *
 * Created by PhpStorm.
 * User: jepson
 * Date: 16/8/3
 * Time: 下午9:17
 */
class DbPool
{
    public static $_instance = null;

    /**
     * mysql 对象
     * @var null
     */
    protected $_mysql = null;

    /**
     * 配置文件
     * @var null
     */
    protected $_config = null;

    /**
     * 是否为异步
     * @var bool
     */
    protected $_async = false;

    /**
     * 连接池大小
     * @var int
     */
    protected $_pool_size = 0;

    /**
     * buildQuery对象
     * @var null
     */
    protected $_select = null;

    public static function getInstance($config = array())
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self($config);
        }
        return self::$_instance;
    }

    private function __construct($config)
    {
        if (empty($config['host']) ||
            empty($config['database']) ||
            empty($config['user']) ||
            empty($config['password'])
        ) {
            throw new \Exception("require host, database, user, password config.");
        }

        if (empty($config['port'])) {
            $config['port'] = 3306;
        }

        if (empty($config['charset'])) {
            $config['charset'] = 'UTF-8';
        }

        //异步mysql
        $this->_async = isset($config['async']) ? intval($config['async']) : false;
        if ($this->_async) {
            $this->_pool_size = (isset($config['pool_size']) && intval($config['pool_size']) > 0) ? intval($config['pool_size']) : 10;
        }

        $this->_config = $config;
    }

    /**
     * 获取mysql
     * @return null
     */
    public function mysql()
    {
        if (is_null($this->_mysql)) {
            if ($this->_async) {
                $this->_mysql = new AsyncMysqliPool($this->_config, $this->_pool_size);
                //可以设置等待队列上限
            } else {

            }
        }

        return $this->_mysql;
    }

    /**
     * 获取sql语句解析器
     * @return BuildQuery|null
     */
    public function select()
    {
        if (is_null($this->_select)) {
            $this->_select = MysqlBuildQuery::getInstance();
        }

        return $this->_select;
    }

    /**
     * 异步执行sql语句
     * @param $query
     * @param callable $callback
     * @param null $socket
     * @return mixed
     */
    public function asyncQuery($query, callable $callback, $socket = null)
    {
        return $this->mysql()->query($query, $callback, $socket, is_null($socket) ? false : true);
    }

    /**
     * 异步查询
     * @param null $numRows
     * @param string $columns
     * @param callable $callback
     * @return mixed
     */
    public function asyncGet($numRows = null, $columns = '*', callable $callback)
    {
        $column = empty($columns) ? '*' : (is_array($columns) ? implode(', ', $columns) : $columns);
        $query = $this->select()->selectSql($numRows, $column);
        return $this->mysql()->query($query, $callback);//todo 能否在包一层方法
    }

    /**
     * 异步查询一行
     * @param string $columns
     * @param callable $callback
     * @return mixed
     */
    public function asyncGetRow($columns = "*", callable $callback)
    {
        return $this->asyncGet(1, $columns, $callback);
    }

    /**
     * 异步查询一个
     * @param $column
     * @param callable $callback
     * @return mixed
     */
    public function asyncGetOne($column, callable $callback)
    {
        return $this->asyncGet(1, $column, $callback);
    }

    /**
     * 异步插入
     * @param $data
     * @param callable $callback
     * @param null $socket
     * @return mixed
     */
    public function asyncInsert($data, callable $callback, $socket = null)
    {
        $query = $this->select()->insertSql($data);
        return $this->mysql()->query($query, $callback, $socket, is_null($socket) ? false : true);
    }

    /**
     * 异步更新
     * @param $data
     * @param callable $callback
     * @param null $socket
     * @return mixed
     */
    public function asyncUpdate($data, callable $callback, $socket = null)
    {
        $query = $this->select()->updateSql($data);
        return $this->mysql()->query($query, $callback, $socket, is_null($socket) ? false : true);
    }

    /**
     * 异步删除
     * @param callable $callback
     * @param null $socket
     * @return mixed
     */
    public function asyncDelete(callable $callback, $socket = null)
    {
        $query = $this->select()->deleteSql();
        return $this->mysql()->query($query, $callback, $socket, is_null($socket) ? false : true);
    }

    /**
     * 异步查询表详情
     * @param $tables
     * @param callable $callback
     * @param string $columns
     * @return mixed
     */
    public function asyncTableExists($tables, callable $callback, $columns = "table_name")
    {
        $tables = (array)$tables;
        empty($tables) && call_user_func($callback, false);

        $query = $this->select()->tableExistsSql($this->_config['database'], $tables, $columns);
        return $this->mysql()->query($query, $callback);
    }

    /**
     * 开启事务自动提交
     * @param callable $callback
     * @param null $socket
     * @return mixed
     */
    public function autoCommit(callable $callback, $socket)
    {
        $query = $this->select()->autocommitSql(true);

        return $this->mysql()->query($query, $callback, $socket, false);
    }

    /**
     * 关闭自动事务提交
     * @param callable $callback
     * @return mixed
     */
    public function disableAutoCommit(callable $callback)
    {
        $query = $this->select()->autocommitSql(false);

        return $this->mysql()->query($query, $callback, null, true);
    }

    /**
     * 开启事务
     * @param $socket
     * @param callable $callback
     * @return mixed
     */
    public function startTransaction($socket, callable $callback)
    {
        $query = $this->select()->startTransactionSql();

        return $this->mysql()->query($query, $callback, $socket, true);
    }

    /**
     * 提交事务
     * @param $socket
     * @param callable $callback
     * @return mixed
     */
    public function commit($socket, callable $callback)
    {
        $query = $this->select()->commitSql();

        return $this->mysql()->query($query, $callback, $socket, true);
    }

    /**
     * 回滚事务
     * @param $socket
     * @param callable $callback
     * @return mixed
     */
    public function rollback($socket, callable $callback)
    {
        $query = $this->select()->rollbackSql();

        return $this->mysql()->query($query, $callback, $socket, true);
    }
}