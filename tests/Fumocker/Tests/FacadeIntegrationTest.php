<?php
namespace Fumocker\Tests;

use Fumocker\Facade;
use Fumocker\MockGenerator;
use Fumocker\CallbackRegistry;

class FacadeIntegrationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Fumocker\Facade
     */
    protected static $fumocker;

    public static function setUpBeforeClass()
    {
        self::$fumocker = new Facade(new MockGenerator(), CallbackRegistry::getInstance());
    }

    public function tearDown()
    {
        self::$fumocker->setGlobals();
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

        self::$fumocker->set($namespace, $function, array($mock, $function));

        return $mock;
    }
}
