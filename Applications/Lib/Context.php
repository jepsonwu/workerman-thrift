<?php
namespace Application\Lib;

use Application\Exception\CommonException;

/**
 * 服务逻辑层上下文处理  request response todo 重写
 * Created by PhpStorm.
 * User: jepson
 * Date: 16/7/29
 * Time: 上午11:36
 */
class Context
{
    protected $_protocol = null;

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

    public function setProtocol($protocol)
    {
        $this->_protocol = $protocol;
    }

    public function getProtocol()
    {
        return $this->_protocol;
    }

    public function successReturn($data = array())
    {
        $return = array(
            "code" => CommonException::SUCCESS,
            "msg" => "",
            "data" => $data
        );
        return $this->response($return);
    }

    public function failedReturn($code, $msg, $data = array())
    {
        $return = array(
            "code" => $code,
            "msg" => $msg,
            "data" => $data
        );
        return $this->response($return);
    }

    protected function response($data)
    {
        switch ($this->_protocol) {
            case "json":
            default:
                return json_encode($data);
                break;
        }
    }
}