<?php
use PHPUnit\Framework\TestCase;

/**
 * @group jepson
 * helloWorld 测试单元
 * Created by PhpStorm.
 * User: jepson
 * Date: 16/7/29
 * Time: 下午5:11
 */
class HelloWorldTest extends TestCase
{
//    /**
//     * @codeCoverageIgnore
//     * 加载服务文件
//     */
//    public function testRequire()
//    {
//        foreach (glob(realpath(__DIR__ . "/../ThriftRpc/") . "/Services/HelloWorld/*.php") as $php_file) {
//            require_once $php_file;
//        }
//    }

    /**
     * 生产者返回测试基境
     * @return \Services\HelloWorld\HelloWorldHandler
     */
    public function testSayHelloConstruct()
    {
        return new Application\Services\HelloWorldService();
    }

    /**
     * 测试参数格式
     * @depends testSayHelloConstruct
     * @expectedException \Application\Exceptions\HelloWorldException
     * @expectedExceptionCode \Application\Exceptions\HelloWorldException::INVALID_ARGUMENTS
     * @param $helloWorldService
     */
    public function testSayHelloArguments($helloWorldService)
    {
        $helloWorldService->sayHello("123jepson");
    }

    /**
     * @depends testSayHelloConstruct
     * @param $helloWorldService
     */
    public function testSayHello($helloWorldService)
    {
        $this->assertEquals("Hello jepson", $helloWorldService->sayHello("jepson"), "sayHello 返回错误!");
    }
}