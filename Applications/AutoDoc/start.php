<?php
use \Workerman\WebServer;

/**
 * 自动化文档
 * Created by PhpStorm.
 * User: jepson
 * Date: 16/8/16
 * Time: 下午3:04
 */
require_once __DIR__ . "/../../Workerman/Autoloader.php";


$auto_doc = new WebServer("http://0.0.0.0:8080");
$auto_doc->name = "AutoDoc";
$auto_doc->addRoot('www.your_domain.com', __DIR__ . '/Web');