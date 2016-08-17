<?php
use Zend\Mvc\Application;

/**
 * Created by PhpStorm.
 * User: jepson
 * Date: 16/8/16
 * Time: 下午3:25
 */

//define
!defined('APPLICATION_PATH') && define('APPLICATION_PATH', realpath(__DIR__ . "/../") . "/");

$env = getenv("WORKERMAN_THRIFT_ENV");
!defined("APPLICATION_ENV") && define("APPLICATION_ENV", $env ? strtolower($env) : "development");
unset($env);

require_once APPLICATION_PATH . "../../vendor/autoload.php";

//config

Application::init()->run();