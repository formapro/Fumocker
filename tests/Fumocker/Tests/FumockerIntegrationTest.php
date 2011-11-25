<?php
namespace Fumocker\Tests;

use Fumocker\Fumocker;

class FumockerIntegrationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Fumocker\Fumocker
     */
    protected $fumocker;

    public function setUp()
    {
        $this->fumocker = new Fumocker();
    }

    public function tearDown()
    {
        $this->fumocker->setGlobals();
    }

    /**
     * @test
     */
    public function shouldMockRangeFunctionAndUseItsMock()
    {
        $functionMock = $this->getFunctionMock(__NAMESPACE__, 'range');
        $functionMock
            ->expects($this->once())
            ->method('range')
            ->with(
                $this->equalTo(4),
                $this->equalTo(5)
            )
            ->will($this->returnValue(array(2, 3)))
        ;

        $result = range(4, 5);

        $this->assertEquals(array(2, 3), $result);
    }

    /**
     * @test
     */
    public function shouldSetGlobalFunctionAsCallable()
    {
        $this->assertEquals(array(4, 5), range(4, 5));
    }

    public function getFunctionMock($namespace, $function)
    {
        $mock = $this->getMock('\stdClass', array($function));

        $this->fumocker->set($namespace, $function, array($mock, $function));

        return $mock;
    }
}
