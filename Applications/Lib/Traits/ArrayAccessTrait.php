<?php
namespace Application\Lib\Traits;

/**
 *
 * arrayAccessTrait
 * Created by PhpStorm.
 * User: jepson
 * Date: 16/8/30
 * Time: 下午2:38
 */
trait ArrayAccessTrait
{
    public function offsetExists($offset)
    {
        return isset($this->$offset);
    }

    public function offsetGet($offset)
    {
        return $this->$offset;
    }

    public function offsetSet($offset, $value)
    {
        $this->$offset = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->$offset);
    }
}