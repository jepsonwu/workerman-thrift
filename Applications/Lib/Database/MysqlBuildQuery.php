<?php
namespace Application\Lib\Database;

use \Exception;

/**
 * 构造查询语句类
 *
 * @link      http://github.com/joshcam/PHP-MySQLi-Database-Class
 * Created by PhpStorm.
 * User: jepson
 * Date: 16/8/4
 * Time: 上午10:14
 */
class MysqlBuildQuery
{
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
     * 表前缀
     * @var string
     */
    protected $_prefix = '';

    /**
     * 表名
     * @var string
     */
    protected $_tableName = '';

    /**
     * 当前执行语句
     * @var
     */
    protected $_query;

    /**
     * 条件
     * @var array
     */
    protected $_where = array();

    /**
     * having
     * @var array
     */
    protected $_having = array();

    /**
     * 排序
     * @var array
     */
    protected $_orderBy = array();

    /**
     * 聚合
     * @var array
     */
    protected $_groupBy = array();

    /**
     * 绑定字段
     * @var array
     */
    protected $_bindParams = array('');

    /**
     * 查询选项
     * @var array
     */
    protected $_queryOptions = array();

    protected $_nestJoin = false;

    /**
     * 排它锁
     * @var bool
     */
    protected $_forUpdate = false;

    /**
     * 共享锁
     * @var bool
     */
    protected $_lockInShareMode = false;

    /**
     * 连表
     * @var array
     */
    protected $_join = array();

    /**
     * 连表条件
     * @var array
     */
    protected $_joinAnd = array();

    /**
     * 插入更新
     * @var null
     */
    protected $_updateColumns = null;

    /**
     * 最后执行语句
     * @var
     */
    protected $_lastQuery = '';

    /**
     * 设置表前缀
     * @param $preix
     * @return $this
     */
    public function setPrefix($preix)
    {
        $this->_prefix = $preix;
        return $this;
    }

    /**
     * 返回表前缀
     * @return string
     */
    public function getPrefix()
    {
        return $this->_prefix;
    }

    /**
     * 设置表名
     * @param $tablename
     * @return $this
     */
    public function setTable($tablename)
    {
        if (strpos($tablename, '.') === false) {
            $this->_tableName = $this->_prefix . $tablename;
        } else {
            $this->_tableName = $tablename;
        }

        return $this;
    }

    /**
     * 返回表名
     * @return string
     */
    public function getTable()
    {
        return $this->_tableName;
    }

    /**
     *
     * 设置查询选项
     * @param $options
     * @return $this
     * @throws Exception
     */
    public function setQueryOption($options)
    {
        $allowedOptions = Array('ALL', 'DISTINCT', 'DISTINCTROW', 'HIGH_PRIORITY', 'STRAIGHT_JOIN', 'SQL_SMALL_RESULT',
            'SQL_BIG_RESULT', 'SQL_BUFFER_RESULT', 'SQL_CACHE', 'SQL_NO_CACHE', 'SQL_CALC_FOUND_ROWS',
            'LOW_PRIORITY', 'IGNORE', 'QUICK', 'MYSQLI_NESTJOIN', 'FOR UPDATE', 'LOCK IN SHARE MODE');

        if (!is_array($options)) {
            $options = Array($options);
        }

        foreach ($options as $option) {
            $option = strtoupper($option);
            if (!in_array($option, $allowedOptions)) {
                throw new Exception('Wrong query option: ' . $option);
            }

            if ($option == 'MYSQLI_NESTJOIN') {
                $this->_nestJoin = true;
            } elseif ($option == 'FOR UPDATE') {
                $this->_forUpdate = true;
            } elseif ($option == 'LOCK IN SHARE MODE') {
                $this->_lockInShareMode = true;
            } else {
                $this->_queryOptions[] = $option;
            }
        }

        return $this;
    }

    /**
     * 设置条件
     * @param $whereProp
     * @param string $whereValue
     * @param string $operator
     * @param string $cond
     * @return $this
     */
    public function where($whereProp, $whereValue = 'DBNULL', $operator = '=', $cond = 'AND')
    {
        // forkaround for an old operation api
        if (is_array($whereValue) && ($key = key($whereValue)) != "0") {
            $operator = $key;
            $whereValue = $whereValue[$key];
        }

        if (count($this->_where) == 0) {
            $cond = '';
        }

        $this->_where[] = array($cond, $whereProp, $operator, $whereValue);
        return $this;
    }

    /**
     * or where
     * @param $whereProp
     * @param string $whereValue
     * @param string $operator
     * @return BuildQuery
     */
    public function orWhere($whereProp, $whereValue = 'DBNULL', $operator = '=')
    {
        return $this->where($whereProp, $whereValue, $operator, 'OR');
    }

    /**
     * having
     * @param $havingProp
     * @param string $havingValue
     * @param string $operator
     * @param string $cond
     * @return $this
     */
    public function having($havingProp, $havingValue = 'DBNULL', $operator = '=', $cond = 'AND')
    {
        // forkaround for an old operation api
        if (is_array($havingValue) && ($key = key($havingValue)) != "0") {
            $operator = $key;
            $havingValue = $havingValue[$key];
        }

        if (count($this->_having) == 0) {
            $cond = '';
        }

        $this->_having[] = array($cond, $havingProp, $operator, $havingValue);
        return $this;
    }

    /**
     * orhaving
     * @param $havingProp
     * @param null $havingValue
     * @param null $operator
     * @return BuildQuery
     */
    public function orHaving($havingProp, $havingValue = null, $operator = null)
    {
        return $this->having($havingProp, $havingValue, $operator, 'OR');
    }

    /**
     * order by
     * @param $orderByField
     * @param string $orderbyDirection
     * @param null $customFields
     * @return $this
     * @throws Exception
     */
    public function orderBy($orderByField, $orderbyDirection = "DESC", $customFields = null)
    {
        $allowedDirection = Array("ASC", "DESC");
        $orderbyDirection = strtoupper(trim($orderbyDirection));
        $orderByField = preg_replace("/[^-a-z0-9\.\(\),_`\*\'\"]+/i", '', $orderByField);

        // Add table prefix to orderByField if needed.
        //FIXME: We are adding prefix only if table is enclosed into `` to distinguish aliases
        // from table names
        $orderByField = preg_replace('/(\`)([`a-zA-Z0-9_]*\.)/', '\1' . $this->_prefix . '\2', $orderByField);


        if (empty($orderbyDirection) || !in_array($orderbyDirection, $allowedDirection)) {
            throw new Exception('Wrong order direction: ' . $orderbyDirection);
        }

        if (is_array($customFields)) {
            foreach ($customFields as $key => $value) {
                $customFields[$key] = preg_replace("/[^-a-z0-9\.\(\),_` ]+/i", '', $value);
            }

            $orderByField = 'FIELD (' . $orderByField . ', "' . implode('","', $customFields) . '")';
        }

        $this->_orderBy[$orderByField] = $orderbyDirection;
        return $this;
    }

    /**
     * groupby
     * @param $groupByField
     * @return $this
     */
    public function groupBy($groupByField)
    {
        $groupByField = preg_replace("/[^-a-z0-9\.\(\),_\*]+/i", '', $groupByField);

        $this->_groupBy[] = $groupByField;
        return $this;
    }

    /**
     * 设置join选项
     * @param $joinTable
     * @param $joinCondition
     * @param string $joinType
     * @return $this
     * @throws Exception
     */
    public function join($joinTable, $joinCondition, $joinType = '')
    {
        $allowedTypes = array('LEFT', 'RIGHT', 'OUTER', 'INNER', 'LEFT OUTER', 'RIGHT OUTER');
        $joinType = strtoupper(trim($joinType));

        if ($joinType && !in_array($joinType, $allowedTypes)) {
            throw new Exception('Wrong JOIN type: ' . $joinType);
        }

        if (!is_object($joinTable)) {
            $joinTable = $this->_prefix . $joinTable;
        }

        $this->_join[] = Array($joinType, $joinTable, $joinCondition);

        return $this;
    }

    /**
     * 连表条件
     * @param $whereJoin
     * @param $whereProp
     * @param string $whereValue
     * @param string $operator
     * @param string $cond
     * @return $this
     */
    public function joinWhere($whereJoin, $whereProp, $whereValue = 'DBNULL', $operator = '=', $cond = 'AND')
    {
        $this->_joinAnd[$whereJoin][] = Array($cond, $whereProp, $operator, $whereValue);
        return $this;
    }

    /**
     * 连表或条件
     * @param $whereJoin
     * @param $whereProp
     * @param string $whereValue
     * @param string $operator
     * @param string $cond
     * @return BuildQuery
     */
    public function joinOrWhere($whereJoin, $whereProp, $whereValue = 'DBNULL', $operator = '=', $cond = 'AND')
    {
        return $this->joinWhere($whereJoin, $whereProp, $whereValue, $operator, 'OR');
    }

    /**
     * 返回查询语句
     * @param $column
     * @param $numRows
     * @return string
     */
    public function selectSql($numRows, $column)
    {
        $this->_query = 'SELECT ' . implode(' ', $this->_queryOptions) . ' ' .
            $column . " FROM " . $this->_tableName;
        return $this->_buildQuery($numRows);
    }

    /**
     * 返回插入语句
     * @param $insertData
     * @return string
     */
    public function insertSql($insertData)
    {
        $this->_query = "INSERT " . implode(' ', $this->_queryOptions) . " INTO " . $this->_tableName;
        return $this->_buildQuery(null, $insertData);
    }

    /**
     * 返回更新语句
     * @param $tableData
     * @param null $numRows
     * @return string
     */
    public function updateSql($tableData, $numRows = null)
    {
        $this->_query = "UPDATE " . $this->_tableName;

        return $this->_buildQuery($numRows, $tableData);
    }

    /**
     * 返回删除语句
     * @param null $numRows
     * @return string
     */
    public function deleteSql($numRows = null)
    {
        if (count($this->_join)) {
            $this->_query = "DELETE " . preg_replace('/.* (.*)/', '$1', $this->_tableName) . " FROM " . $this->_tableName;
        } else {
            $this->_query = "DELETE FROM " . $this->_tableName;
        }

        return $this->_buildQuery($numRows);
    }

    /**
     * 构建表详情语句
     * @param $db
     * @param $tables
     * @param string $columns
     * @return string
     */
    public function tableExistsSql($db, $tables, $columns = "*")
    {
        array_walk($tables, function (&$val) {
            $val = $this->_prefix . $val;
        });
        $this->setTable("information_schema.tables");

        $this->where('table_schema', $db);
        $this->where('table_name', $tables, 'in');
        return $this->selectSql(count($tables), $columns);
    }

    /**
     * 开始事务语句
     * @return string
     */
    public function startTransactionSql()
    {
        $this->_lastQuery = "START TRANSACTION";
        $this->reset();
        return $this->_lastQuery;
    }

    /**
     * 提交事务语句
     * @return string
     */
    public function commitSql()
    {
        $this->_lastQuery = "COMMIT";
        $this->reset();
        return $this->_lastQuery;
    }

    /**
     * 回滚事务语句
     * @return string
     */
    public function rollbackSql()
    {
        $this->_lastQuery = "ROLLBACK";
        $this->reset();
        return $this->_lastQuery;
    }

    /**
     * 设置自动提交语句
     * @param bool $true
     * @return string
     */
    public function autocommitSql($true = true)
    {
        $this->_lastQuery = "SET AUTOCOMMIT =" . intval($true);
        $this->reset();
        return $this->_lastQuery;
    }

    /**
     * 返回最后执行语句
     * @return string
     */
    public function getLastQuery()
    {
        return $this->_lastQuery;
    }

    /**
     * 重置查询条件
     */
    public function reset()
    {
        $this->_where = array();
        $this->_having = array();
        $this->_join = array();
        $this->_joinAnd = array();
        $this->_orderBy = array();
        $this->_groupBy = array();
        $this->_bindParams = array('');
        $this->_query = null;
        $this->_queryOptions = array();
        $this->_nestJoin = false;
        $this->_forUpdate = false;
        $this->_lockInShareMode = false;
        //$this->_tableName = '';
        $this->_updateColumns = null;
    }

    /**
     * 构建执行语句
     * @param null $numRows
     * @param null $tableData
     * @return string
     */
    protected function _buildQuery($numRows = null, $tableData = null)
    {
        $this->_buildJoin();
        $this->_buildInsertQuery($tableData);
        $this->_buildCondition('WHERE', $this->_where);
        $this->_buildGroupBy();
        $this->_buildCondition('HAVING', $this->_having);
        $this->_buildOrderBy();
        $this->_buildLimit($numRows);
//        $this->_buildOnDuplicate($tableData);

        if ($this->_forUpdate) {
            $this->_query .= ' FOR UPDATE';
        }
        if ($this->_lockInShareMode) {
            $this->_query .= ' LOCK IN SHARE MODE';
        }

        $this->_lastQuery = $this->replacePlaceHolders($this->_query, $this->_bindParams);
        $this->reset();

        return $this->_lastQuery;
    }

    /**
     * 构建连表
     */
    protected function _buildJoin()
    {
        if (empty ($this->_join))
            return;

        foreach ($this->_join as $data) {
            list ($joinType, $joinTable, $joinCondition) = $data;

//            if (is_object($joinTable))
//                $joinStr = $this->_buildPair("", $joinTable);
//            else
            $joinStr = $joinTable;

            $this->_query .= " " . $joinType . " JOIN " . $joinStr . " on " . $joinCondition;

            // Add join and query
            if (!empty($this->_joinAnd) && isset($this->_joinAnd[$joinStr])) {
                foreach ($this->_joinAnd[$joinStr] as $join_and_cond) {
                    list ($concat, $varName, $operator, $val) = $join_and_cond;
                    $this->_query .= " " . $concat . " " . $varName;
                    $this->conditionToSql($operator, $val);
                }
            }
        }
    }

    /**
     * 构建插入语句
     * @param $tableData
     * @throws Exception
     */
    protected function _buildInsertQuery($tableData)
    {
        if (!is_array($tableData)) {
            return;
        }

        $isInsert = preg_match('/^[INSERT|REPLACE]/', $this->_query);
        $dataColumns = array_keys($tableData);
        if ($isInsert) {
            if (isset ($dataColumns[0]))
                $this->_query .= ' (`' . implode($dataColumns, '`, `') . '`) ';
            $this->_query .= ' VALUES (';
        } else {
            $this->_query .= " SET ";
        }

        $this->_buildDataPairs($tableData, $dataColumns, $isInsert);

        if ($isInsert) {
            $this->_query .= ')';
        }
    }

    /**
     * 构建聚合
     */
    protected function _buildGroupBy()
    {
        if (empty($this->_groupBy)) {
            return;
        }

        $this->_query .= " GROUP BY ";

        foreach ($this->_groupBy as $key => $value) {
            $this->_query .= $value . ", ";
        }

        $this->_query = rtrim($this->_query, ', ') . " ";
    }

    /**
     * 构建排序
     */
    protected function _buildOrderBy()
    {
        if (empty($this->_orderBy)) {
            return;
        }

        $this->_query .= " ORDER BY ";
        foreach ($this->_orderBy as $prop => $value) {
            if (strtolower(str_replace(" ", "", $prop)) == 'rand()') {
                $this->_query .= "rand(), ";
            } else {
                $this->_query .= $prop . " " . $value . ", ";
            }
        }

        $this->_query = rtrim($this->_query, ', ') . " ";
    }

    /**
     * 构建limit
     * @param $numRows
     */
    protected function _buildLimit($numRows)
    {
        if (!isset($numRows)) {
            return;
        }

        if (is_array($numRows)) {
            $this->_query .= ' LIMIT ' . (int)$numRows[0] . ', ' . (int)$numRows[1];
        } else {
            $this->_query .= ' LIMIT ' . (int)$numRows;
        }
    }

    /**
     * 插入更新 todo bug
     * @param $tableData
     * @param $tableColumns
     * @param $isInsert
     * @throws Exception
     */
    public function _buildDataPairs($tableData, $tableColumns, $isInsert)
    {
        foreach ($tableColumns as $column) {
            $value = $tableData[$column];

            if (!$isInsert) {
                if (strpos($column, '.') === false) {
                    $this->_query .= "`" . $column . "` = ";
                } else {
                    $this->_query .= str_replace('.', '.`', $column) . "` = ";
                }
            }

            // Subquery value
//            if ($value instanceof MysqliDb) {
//                $this->_query .= $this->_buildPair("", $value) . ", ";
//                continue;
//            }

            // Simple value
            if (!is_array($value)) {
                $this->_bindParam($value);
                $this->_query .= '?, ';
                continue;
            }

            // Function value
            $key = key($value);
            $val = $value[$key];
            switch ($key) {
                case '[I]':
                    $this->_query .= $column . $val . ", ";
                    break;
                case '[F]':
                    $this->_query .= $val[0] . ", ";
                    if (!empty($val[1])) {
                        $this->_bindParams($val[1]);
                    }
                    break;
                case '[N]':
                    if ($val == null) {
                        $this->_query .= "!" . $column . ", ";
                    } else {
                        $this->_query .= "!" . $val . ", ";
                    }
                    break;
                default:
                    throw new Exception("Wrong operation");
            }
        }
        $this->_query = rtrim($this->_query, ', ');
    }

    /**
     * 填充值
     * @param $str
     * @param $vals
     * @return string
     */
    protected function replacePlaceHolders($str, $vals)
    {
        $i = 1;
        $newStr = "";

        if (empty($vals)) {
            return $str;
        }

        while ($pos = strpos($str, "?")) {
            $val = $vals[$i++];
            if (is_object($val)) {
                $val = '[object]';
            }
            if ($val === null) {
                $val = 'NULL';
            }
            $newStr .= substr($str, 0, $pos) . "'" . $val . "'";
            $str = substr($str, $pos + 1);
        }
        $newStr .= $str;
        return $newStr;
    }

    /**
     *
     * @param $operator
     * @param $val
     */
    private function conditionToSql($operator, $val)
    {
        switch (strtolower($operator)) {
            case 'not in':
            case 'in':
                $comparison = ' ' . $operator . ' (';
                if (is_object($val)) {
                    $comparison .= $this->_buildPair("", $val);
                } else {
                    foreach ($val as $v) {
                        $comparison .= ' ?,';
                        $this->_bindParam($v);
                    }
                }
                $this->_query .= rtrim($comparison, ',') . ' ) ';
                break;
            case 'not between':
            case 'between':
                $this->_query .= " $operator ? AND ? ";
                $this->_bindParams($val);
                break;
            case 'not exists':
            case 'exists':
                $this->_query .= $operator . $this->_buildPair("", $val);
                break;
            default:
                if (is_array($val))
                    $this->_bindParams($val);
                else if ($val === null)
                    $this->_query .= $operator . " NULL";
                else if ($val != 'DBNULL' || $val == '0')
                    $this->_query .= $this->_buildPair($operator, $val);
        }
    }

    /**
     *构建条件
     * @param $operator
     * @param $conditions
     */
    protected function _buildCondition($operator, &$conditions)
    {
        if (empty($conditions)) {
            return;
        }

        //Prepare the where portion of the query
        $this->_query .= ' ' . $operator;

        foreach ($conditions as $cond) {
            list ($concat, $varName, $operator, $val) = $cond;
            $this->_query .= " " . $concat . " " . $varName;

            switch (strtolower($operator)) {
                case 'not in':
                case 'in':
                    $comparison = ' ' . $operator . ' (';
//                    if (is_object($val)) {
//                        $comparison .= $this->_buildPair("", $val);
//                    } else {
                    foreach ($val as $v) {
                        $comparison .= ' ?,';
                        $this->_bindParam($v);
                    }
                    $this->_query .= rtrim($comparison, ',') . ' ) ';
                    break;
                case 'not between':
                case 'between':
                    $this->_query .= " $operator ? AND ? ";
                    $this->_bindParams($val);
                    break;
                case 'not exists':
                case 'exists':
                    $this->_query .= $operator . $this->_buildPair("", $val);
                    break;
                default:
                    if (is_array($val)) {
                        $this->_bindParams($val);
                    } elseif ($val === null) {
                        $this->_query .= ' ' . $operator . " NULL";
                    } elseif ($val != 'DBNULL' || $val == '0') {
                        $this->_query .= $this->_buildPair($operator, $val);
                    }
            }
        }
    }

    /**
     *
     * @param $operator
     * @param $value
     * @return string
     */
    protected function _buildPair($operator, $value)
    {
        $this->_bindParam($value);
        return ' ' . $operator . ' ? ';
    }

    /**
     *
     * @param $values
     */
    protected function _bindParams($values)
    {
        foreach ($values as $value) {
            $this->_bindParam($value);
        }
    }

    /**
     * 绑定参数
     * @param $value
     */
    protected function _bindParam($value)
    {
        $this->_bindParams[0] .= $this->_determineType($value);
        array_push($this->_bindParams, $value);
    }

    /**
     * 参数类型
     * @param $item
     * @return string
     */
    protected function _determineType($item)
    {
        switch (gettype($item)) {
            case 'NULL':
            case 'string':
                return 's';
                break;

            case 'boolean':
            case 'integer':
                return 'i';
                break;

            case 'blob':
                return 'b';
                break;

            case 'double':
                return 'd';
                break;
        }
        return '';
    }
}