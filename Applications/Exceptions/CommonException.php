<?php
namespace Application\Exceptions;
/**
 * 公共错误异常处理
 * Created by PhpStorm.
 * User: jepson
 * Date: 16/7/29
 * Time: 上午11:23
 */

class CommonException extends \Exception
{
    const SUCCESS = 0;//成功


    const INVALID_ARGUMENTS = 1001;//参数错误

    const SYSTEM_EXCEPTION = 1002;//系统异常  例如DB连接错误

    const INVALID_MOBILE = 2001;//手机号不正确
}