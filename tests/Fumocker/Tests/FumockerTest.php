<?php
namespace Fumocker\Tests;

use Fumocker\Fumocker;
use Fumocker\MockGenerator;

class FacadeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldRequiredGeneratorAndRegistrySetInConstructor()
    {
        new Fumocker(
            $this->createGeneratorMock(),
            $this->createCallbackRegistryMock()
        );
    }

    /**
     * @test
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage The global function with name `foo` does not exist.
     */
    public function throwWhileSettingCallableIfGlobalFunctionWithThisNameNotExist()
    {
        $facade = new Fumocker(
            $this->createGeneratorMock(),
            $this->createCallbackRegistryMock()
        );

        $facade->set('Bar', 'foo', function() {});
    }

    /**
     * @test
     */
    public function shouldGenerateFunctionMockAndSetCallableToRegistry()
    {
        $namespace = 'Bar';
        $functionName = 'mail';
        $callable = function() {};

        $generatorMock = $this->createGeneratorMock();
        $generatorMock
            ->expects($this->once())
            ->method('generate')
            ->with(
                $this->equalTo($namespace),
                $this->equalTo($functionName)
            )
        ;
        $generatorMock
            ->expects($this->once())
            ->method('hasGenerated')
            ->will($this->returnValue(false));

        $registryMock = $this->createCallbackRegistryMock();
        $registryMock
            ->expects($this->once())
            ->method('set')
            ->with(
                $this->equalTo($namespace),
                $this->equalTo($functionName),
                $this->equalTo($callable)
            )
        ;

        $facade = new Fumocker($generatorMock, $registryMock);

        $facade->set($namespace, $functionName, $callable);
    }

    /**
     * @test
     */
    public function shouldNotGenerateFunctionIfWasGeneratedAndSetCallableToRegistry()
    {
        $namespace = 'Bar';
        $functionName = 'mail';
        $callable = function() {};

        $generatorMock = $this->createGeneratorMock();
        $generatorMock
            ->expects($this->never())
            ->method('generate')
        ;
        $generatorMock
            ->expects($this->once())
            ->method('hasGenerated')
            ->will($this->returnValue(true));

        $registryMock = $this->createCallbackRegistryMock();
        $registryMock
            ->expects($this->once())
            ->method('set')
            ->with(
                $this->equalTo($namespace),
                $this->equalTo($functionName),
                $this->equalTo($callable)
            )
        ;

        $facade = new Fumocker($generatorMock, $registryMock);

        $facade->set($namespace, $functionName, $callable);
    }

    /**
     * @test
     */
    public function shouldAllowToSetGlobalCallablesForMockedFunctions()
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
                array($firstNamespace, $firstFunctionName, $firstCallable),
                array($secondNamespace, $secondFunctionName, $secondCallable),
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

        $facade = new Fumocker($generatorMock, $registryMock);

        $facade->setGlobals();
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
