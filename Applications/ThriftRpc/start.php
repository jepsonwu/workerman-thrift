<?php
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link http://www.workerman.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
use Workerman\Worker;

//bootstrap
require_once __DIR__ . "/../Bootstrap/Bootstrap.php";

//demo
$config = \Application\Lib\Factory::config('mysql');
$db = Application\Lib\Db::getInstance($config);
$select = $db->select()->setTable("user");

function callback($result)
{
    if (is_array($result))
        print_r($result);
    else
        var_dump($result);
}

//$select->where("Type", 2);
//$db->asyncGet(2, "*", 'callback');
//
//$select->where("UID", 3);
//$db->asyncGetRow("*", 'callback');
//$db->asyncGetOne("nickname", 'callback');
$db->asyncInsert(array(
    "Nickname" => "test" . time()
), 'callback');

//$db->asyncTableExists("user","callback");
exit;

//workerman
require_once __DIR__ . '/../../Workerman/Autoloader.php';
require_once __DIR__ . '/ThriftWorker.php';


$worker = new ThriftWorker('tcp://0.0.0.0:9090');
$worker->count = 2;
$worker->class = 'HelloWorld';


// 如果不是在根目录启动，则运行runAll方法
if (!defined('GLOBAL_START')) {
    Worker::runAll();
}
