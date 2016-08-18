<?php
namespace Respect\Validation\Rules;
/**
 * verify the eleven mobile number
 * Created by PhpStorm.
 * User: jepson
 * Date: 16/8/18
 * Time: 上午9:51
 */
class Mobile extends AbstractRule
{
    public function validate($input)
    {
        return preg_match("/^1[3|4|5|7|8][0-9]{9}$/", $input);
    }
}
