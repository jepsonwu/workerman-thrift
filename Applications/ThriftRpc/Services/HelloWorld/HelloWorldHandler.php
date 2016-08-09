<?php
namespace Services\HelloWorld;

use Application\Lib\Factory;
use Application\Services\HelloWorldService;

class HelloWorldHandler implements HelloWorldIf
{
    public function sayHello($name)
    {
        try {
            $helloWorldService = new HelloWorldService();
            $return = $helloWorldService->sayHello($name);
        } catch (\Exception $e) {
            return Factory::exceptionHandler($e);
        }

        //return data what using json protocol
        return Factory::context()->successReturn($return);
    }
}
