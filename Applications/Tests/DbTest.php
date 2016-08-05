<?php
use PHPUnit\Framework\TestCase;

/**
 * @group DB
 * db测试单元
 * todo 异步事务
 * Created by PhpStorm.
 * User: jepson
 * Date: 16/8/5
 * Time: 下午2:43
 */
class DbTest extends TestCase
{
    /**
     * 数据表供给器
     * 使用了数据供给器的测试，其运行结果是无法注入到依赖于此测试的其他测试中的
     * @return array
     */
    public function tableProvider()
    {
        return [
            ['gg'],
        ];
    }

    /**
     * 用于回调DB
     * @var null
     */
    public $_db = null;

    /**
     * 异步事务测试 指定的socket
     * @var null
     */
    public $_socket = null;
    

    /**
     * 初始化DB
     * @return \Application\Lib\Db|null
     */
    public function testDbConstruct()
    {
        $config = \Application\Lib\Factory::config('mysql');
        return Application\Lib\Db::getInstance($config);
    }

    /**
     * 初始化select
     * @depends testDbConstruct
     * @param $db
     * @return mixed
     */
    public function testSelectConstruct($db)
    {
        return $db->select()->setTable("user");
    }

    /**
     * 异步查询表是否存在
     * @dataProvider tableProvider
     * @depends      testDbConstruct
     * @param $table
     * @param $db
     */
    public function testAsyncTableExists($table, $db)
    {
        $db->asyncTableExists($table, function ($result) {
            $this->assertNotEmpty($result, "表名不存在!");
            exit;
        });
    }

    /**
     * 异步查询多条
     * todo 异步编程怎么合理的实现
     * @depends testDbConstruct
     * @depends testSelectConstruct
     * @param $db
     * @param $select
     */
    public function testAsyncGet($db, $select)
    {
        $select->where("Type", 2);
        $db->asyncGet(2, "*", function ($result) {
            $this->assertNotEmpty($result, "异步查询多条失败!");
            exit;//这里要退出
        });
    }

    /**
     * 异步查询一条
     * @depends testDbConstruct
     * @depends testSelectConstruct
     * @param $db
     * @param $select
     */
    public function testAsyncGetRow($db, $select)
    {
        $select->where("UID", 3);
        $db->asyncGetRow("*", function ($result) {
            $this->assertNotEmpty($result, "异步查询一条失败!");
            exit;
        });
    }


    /**
     * 异步查询字段
     * @depends testDbConstruct
     * @depends testSelectConstruct
     * @param $db
     * @param $select
     */
    public function testAsyncGetOne($db, $select)
    {
        $select->where("UID", 3);
        $db->asyncGetOne("nickname", function ($result) {
            $this->assertNotEmpty($result, "异步查询单个字段失败!");
            exit;
        });
    }

    /**
     * 异步事务 指定socket
     * @depends testDbConstruct
     * @param $db
     */
//    public function testAsyncTransaction($db)
//    {
//        $this->_db = $db;
//        $this->_socket = $this->_db->disableAutoCommit(function ($result) {
//            $this->assertTrue($result, "关闭事务自动提交错误!");//断言错误会自动退出,不会再往下执行了
//
//            $this->_db->startTransaction($this->_socket, function ($result) {
//                $this->assertTrue($result, "开启事务失败!");
//
//                //插入第一条数据
//                $this->_db->asyncInsert(array(
//                    "WID" => "5",
//                    "Nickname" => "test" . time()
//                ), function ($result) {
//                    if (!$result) {
//                        $this->_db->rollback($this->_socket, function ($result) {
//                            $this->assertTrue($result, "回滚事务失败!");
//                            exit;
//                        });
//                    }
//
//                    $this->assertTrue($result, "插入第一条数据失败!");
//
//                    //插入第二条数据
//                    $this->_db->asyncInsert(array(
//                        "WID" => "6",
//                        "Nickname" => "test" . time()
//                    ), function ($result) {
//                        if (!$result) {
//                            $this->_db->rollback($this->_socket, function ($result) {
//                                $this->assertTrue($result, "回滚事务失败!");
//                                exit;
//                            });
//                        }
//
//                        $this->assertTrue($result, "插入第二条数据失败!");
//
//                        $this->_db->commit($this->_socket,function($result){
//
//                        });
//                        exit;
//                    }, $this->_socket);
//
//                    exit;
//                }, $this->_socket);
//
//                exit;
//            });
//
//            exit;
//        });
//    }
}