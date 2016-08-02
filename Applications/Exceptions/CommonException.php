<?php
namespace Application\Exceptions;
/**
 * 公共错误代码
 * Created by PhpStorm.
 * User: jepson
 * Date: 16/7/29
 * Time: 上午11:23
 */

class CommonException extends \Exception
{
    const SUCCESS = 0;//成功


    const INVALID_ARGUMENTS = 1001;//参数错误
}