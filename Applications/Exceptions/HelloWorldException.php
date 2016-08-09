<?php
namespace Application\Exceptions;
/**
 * 以服务为单位  定义异常错误处理
 *
 * Created by PhpStorm.
 * User: jepson
 * Date: 16/7/29
 * Time: 上午11:15
 */
class HelloWorldException extends CommonException
{
    const SAY_HELLO_NAME_FAILED = 10001;//名称不符合规范
}