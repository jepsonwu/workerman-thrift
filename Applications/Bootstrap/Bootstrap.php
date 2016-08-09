<?php
/**
 * 启动文件
 * Created by PhpStorm.
 * User: jepson
 * Date: 16/7/30
 * Time: 下午8:45
 */
//init
!defined("APPLICATION_PATH") && define("APPLICATION_PATH", realpath(__DIR__ . "/../") . "/");

$env = getenv("WORKERMAN_THRIFT_ENV");
!defined("APPLICATION_ENV") && define("APPLICATION_ENV", $env ? strtolower($env) : "development");
unset($env);

//application autoloader
require_once APPLICATION_PATH . "Autoloader.php";

//vendor
require_once APPLICATION_PATH . "../vendor/autoload.php";

//timezone
date_default_timezone_set(\Application\Lib\Factory::config('timezone', 'UTC'));

//set encoding
mb_internal_encoding(\Application\Lib\Factory::config('encoding'));