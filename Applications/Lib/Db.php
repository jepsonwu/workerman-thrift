<?php
namespace Application\Lib;

use Application\Lib\Database\AsyncMysqliPool;
use Application\Lib\Database\BuildQuery;

//demo
//$config = \Application\Lib\Factory::config('mysql');
//$db = Application\Lib\Db::getInstance($config);
//$select = $db->select()->setTable("user");
//
//function callback($result)
//{
//    if (is_array($result))
//        print_r($result);
//    else
//        var_dump($result);
//}
//
//$select->where("Type", 2);
//$db->asyncGet(2, "*", 'callback');
//
//$select->where("UID", 3);
//$db->asyncGetRow("*", 'callback');
//$db->asyncGetOne("nickname", 'callback');
//$db->asyncInsert(array(
//    "Nickname" => "test" . time()
//), 'callback');
//exit;

/**
 * db 操作类
 *
 * Created by PhpStorm.
 * User: jepson
 * Date: 16/8/3
 * Time: 下午9:17
 */
class Db
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
            $this->_select = BuildQuery::getInstance();
        }

        return $this->_select;
    }

    /**
     * 异步执行sql语句
     * @param $query
     * @param callable $callback
     */
    public function asyncQuery($query, callable $callback)
    {
        $this->select()->query($query, $callback);
    }

    /**
     * 异步查询
     * @param null $numRows
     * @param string $columns
     * @param callable $callback
     */
    public function asyncGet($numRows = null, $columns = '*', callable $callback)
    {
        $column = empty($columns) ? '*' : (is_array($columns) ? implode(', ', $columns) : $columns);
        $query = $this->select()->selectSql($numRows, $column);
        $this->select()->reset();
        $this->mysql()->query($query, $callback);//todo 能否在包一层方法
    }

    /**
     * 异步查询一行
     * @param string $columns
     * @param callable $callback
     */
    public function asyncGetRow($columns = "*", callable $callback)
    {
        $this->asyncGet(1, $columns, $callback);
    }

    /**
     * 异步查询一个
     * @param $column
     * @param callable $callback
     */
    public function asyncGetOne($column, callable $callback)
    {
        $this->asyncGet(1, $column, $callback);
    }

    /**
     * 异步插入
     * @param $data
     * @param callable $callback
     */
    public function asyncInsert($data, callable $callback)
    {
        $query = $this->select()->insertSql($data);
        $this->select()->reset();
        $this->mysql()->query($query, $callback);
    }

    /**
     * 异步更新
     * @param $data
     * @param callable $callback
     */
    public function asyncUpdate($data, callable $callback)
    {
        $query = $this->select()->updateSql($data);
        $this->select()->reset();
        $this->mysql()->query($query, $callback);
    }

    /**
     * 异步删除
     * @param callable $callback
     */
    public function asyncDelete(callable $callback)
    {
        $query = $this->select()->deleteSql();
        $this->select()->reset();
        $this->mysql()->query($query, $callback);
    }
}