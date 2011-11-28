<?php
namespace Fumocker\Tests;

use Fumocker\Fumocker;
use Fumocker\MockGenerator;

class FumockerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldAcceptOptionalGeneratorAndCallbackRegistryInConstructor()
    {
        $expectedGenerator = $this->createGeneratorMock();
        $expectedRegistry = $this->createCallbackRegistryMock();

        $fumocker = new Fumocker($expectedGenerator, $expectedRegistry);

        $this->assertAttributeSame($expectedGenerator, 'generator', $fumocker);
        $this->assertAttributeSame($expectedRegistry, 'registry', $fumocker);
    }

    /**
     * @test
     */
    public function shouldCreateGeneratorAndCallbackRegistryInConstructorIfNotProvided()
    {
        $fumocker = new Fumocker();

        $this->assertAttributeInstanceOf('Fumocker\MockGenerator', 'generator', $fumocker);
        $this->assertAttributeInstanceOf('Fumocker\CallbackRegistry', 'registry', $fumocker);
    }

    /**
     * @test
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage The global function with name `foo` does not exist.
     */
    public function throwWhileGettingMockOfNotExistGlobalFunction()
    {
        $fumocker = new Fumocker(
            $this->createGeneratorMock(),
            $this->createCallbackRegistryMock()
        );

        $fumocker->getMock('Bar', 'foo');
    }

    /**
     * @test
     */
    public function shouldReturnPhpunitMockObjectWithMethodNamedAsGivenFunction()
    {
        $namespace = 'Bar';
        $function = 'mail';

        $generatorMock = $this->createGeneratorMock();
        $generatorMock
            ->expects($this->any())
            ->method('generate')
        ;

        $registryMock = $this->createCallbackRegistryMock();
        $registryMock
            ->expects($this->any())
            ->method('set')
        ;

        $fumocker = new Fumocker($generatorMock, $registryMock);

        $functionMockObject = $fumocker->getMock($namespace, $function);

        $this->assertInstanceOf('PHPUnit_Framework_MockObject_MockObject', $functionMockObject);
        $this->assertTrue(method_exists($functionMockObject, $function));
    }

    /**
     * @test
     */
    public function shouldGenerateFunctionMockIfNotGenerated()
    {
        $namespace = 'Bar';
        $function = 'mail';

        $generatorMock = $this->createGeneratorMock();
        $generatorMock
            ->expects($this->once())
            ->method('generate')
            ->with(
                $this->equalTo($namespace),
                $this->equalTo($function)
            )
        ;
        $generatorMock
            ->expects($this->once())
            ->method('hasGenerated')
            ->will($this->returnValue(false))
        ;

        $registryMock = $this->createCallbackRegistryMock();
        $registryMock
            ->expects($this->any())
            ->method('set')
        ;

        $fumocker = new Fumocker($generatorMock, $registryMock);

        $fumocker->getMock($namespace, $function);
    }

    /**
     * @test
     */
    public function shouldNotGenerateFunctionMockIfAlreadyGenerated()
    {
        $namespace = 'Bar';
        $function = 'mail';

        $generatorMock = $this->createGeneratorMock();
        $generatorMock
            ->expects($this->never())
            ->method('generate')
        ;
        $generatorMock
            ->expects($this->once())
            ->method('hasGenerated')
            ->will($this->returnValue(true))
        ;

        $registryMock = $this->createCallbackRegistryMock();
        $registryMock
            ->expects($this->any())
            ->method('set')
        ;

        $fumocker = new Fumocker($generatorMock, $registryMock);

        $fumocker->getMock($namespace, $function);
    }

    /**
     * @test
     */
    public function shouldSetPhpunitMockObjectToCallBackRegistryAsCallable()
    {
        $namespace = 'Bar';
        $function = 'mail';

        $generatorMock = $this->createGeneratorMock();
        $generatorMock
            ->expects($this->any())
            ->method('generate')
        ;


        $checker = new \stdClass;
        $checker->actualCallable = null;

        $registryMock = $this->createCallbackRegistryMock();
        $registryMock
            ->expects($this->once())
            ->method('set')
            ->will($this->returnCallback(function($namespace, $function, $callable) use ($checker) {
                $checker->actualNamespace = $namespace;
                $checker->actualFunction = $function;
                $checker->actualCallable = $callable;
            }))
        ;

        $fumocker = new Fumocker($generatorMock, $registryMock);

        $functionMock = $fumocker->getMock($namespace, $function);

        $this->assertEquals($namespace, $checker->actualNamespace);
        $this->assertEquals($function, $checker->actualFunction);
        $this->assertSame(array($functionMock, $function), $checker->actualCallable);
    }

    /**
     * @test
     */
    public function shouldCleanupAllMockedFunctionBySettingGlobalFunctionAsCallable()
    {
        $firstNamespace = 'Foo';
        $firstFunctionName = 'mail';
        $firstCallable = function() {};

        $secondNamespace = 'Bar';
        $secondFunctionName = 'file_get_contents';
        $secondCallable = function() {};

        //guard
        $generatorMock = $this->createGeneratorMock();
        $generatorMock
            ->expects($this->never())
            ->method('generate')
        ;

        $registryMock = $this->createCallbackRegistryMock();
        $registryMock
            ->expects($this->once())
            ->method('getAll')
            ->will($this->returnValue(array(
                array(
                    'namespace' => $firstNamespace,
                    'function' => $firstFunctionName,
                    'callable' => $firstCallable,
                ),
                array(
                    'namespace' => $secondNamespace,
                    'function' => $secondFunctionName,
                    'callable' => $secondCallable
                ),
            )))
        ;
        $registryMock
            ->expects($this->at(1))
            ->method('set')
            ->with(
                $this->equalTo($firstNamespace),
                $this->equalTo($firstFunctionName),
                $this->equalTo($firstFunctionName)
            )
        ;
        $registryMock
            ->expects($this->at(2))
            ->method('set')
            ->with(
                $this->equalTo($secondNamespace),
                $this->equalTo($secondFunctionName),
                $this->equalTo($secondFunctionName)
            )
        ;

        $fumocker = new Fumocker($generatorMock, $registryMock);

        $fumocker->cleanup();
    }

    /**
     * @test
     *
     * @depends shouldReturnPhpunitMockObjectWithMethodNamedAsGivenFunction
     *
     * @expectedException PHPUnit_Framework_ExpectationFailedException
     * @expectedExceptionMessage Method was expected to be called 1 times, actually called 0 times.
     */
    public function shouldVerifyFunctionMockThatItCalledOneTimeWhenInRealNeverCalled()
    {
        $registryMock = $this->createCallbackRegistryMock();
        $registryMock
            ->expects($this->any())
            ->method('getAll')
            ->will($this->returnValue(array()))
        ;

        $fumocker = new Fumocker($this->createGeneratorMock(), $registryMock);

        $functionMock = $fumocker->getMock('Bar', 'mail');

        $functionMock->expects($this->once())->method('mail');

        $fumocker->cleanup();
    }

    /**
     * @test
     *
     * @depends shouldReturnPhpunitMockObjectWithMethodNamedAsGivenFunction
     * @depends shouldVerifyFunctionMockThatItCalledOneTimeWhenInRealNeverCalled
     */
    public function shouldNotVerifyFunctionMockTwice()
    {
        $registryMock = $this->createCallbackRegistryMock();
        $registryMock
            ->expects($this->any())
            ->method('getAll')
            ->will($this->returnValue(array()))
        ;

        $fumocker = new Fumocker($this->createGeneratorMock(), $registryMock);

        $functionMock = $fumocker->getMock('Bar', 'mail');

        $functionMock->expects($this->once())->method('mail');

        try {
            $fumocker->cleanup();

            $this->fail('Cleanup should throw verify exception');
        } catch (\PHPUnit_Framework_ExpectationFailedException $e) { }

        $fumocker->cleanup();
    }

    /**
     * @return \Fumocker\CallbackRegistry
     */
    protected function createCallbackRegistryMock()
    {
        return $this->getMock('Fumocker\CallbackRegistry', array('set', 'get', 'getAll'), array(), '', false);
    }

    /**
     * @return MockGenerator
     */
    protected function createGeneratorMock()
    {
        return $this->getMock('Fumocker\MockGenerator', array('generate', 'hasGenerated'));
    }
}
