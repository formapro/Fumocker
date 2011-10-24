<?php
namespace Fumocker\Tests;

use Fumocker\CallbackRegistry;

class CallbackRegistryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return void
     */
    public function setUp()
    {
        $reflectionClass = new \ReflectionClass('Fumocker\CallbackRegistry');
        $reflectionProperty = $reflectionClass->getProperty('instance');

        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($reflectionClass, null);
        $reflectionProperty->setAccessible(false);
    }

    /**
     * @test
     *
     * @dataProvider provideValidCallbacks
     */
    public function shouldAllowToSetCallable($validCallable)
    {
        CallbackRegistry::getInstance()->set('namespace', 'functionName', $validCallable);
    }

    /**
     * @test
     *
     * @dataProvider provideNoCallableItems
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid callable provided
     */
    public function throwWhenSetInvalidCallable($invalidCallable)
    {
        CallbackRegistry::getInstance()->set('namespace', 'functionName', $invalidCallable);
    }

    /**
     * @test
     *
     * @dataProvider provideNoStrings
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid function name provided. Should be a string
     */
    public function throwWhenSetCallableWithInvalidFunctionName($invalidFunctionName)
    {
        $registry = CallbackRegistry::getInstance();

        $registry->set('namespace', $invalidFunctionName, function(){});
    }

    /**
     * @test
     *
     * @dataProvider provideNoStrings
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid namespace provided. Should be a string
     */
    public function throwWhenSetCallableWithInvalidNamespace($invalidNamespace)
    {
        $registry = CallbackRegistry::getInstance();

        $registry->set($invalidNamespace, 'functionName', function(){});
    }

    /**
     * @test
     */
    public function shouldAllowToGetCallableByFunctionNameAndNamespace()
    {
        $functionName = 'functionFoo';
        $namespace = 'foo';
        $expectedCallable = function(){};

        $registry = CallbackRegistry::getInstance();
        $registry->set($namespace, $functionName, $expectedCallable);

        $actualCallable = $registry->get($namespace, $functionName);

        $this->assertSame($expectedCallable, $actualCallable);
    }

    /**
     * @test
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Cannot find a callable related to foo_ns\bar_func()
     */
    public function throwWhenGetCallableNotSetBefore()
    {
        $registry = CallbackRegistry::getInstance();

        $registry->get('foo_ns', 'bar_func');
    }

    /**
     * @test
     */
    public function shouldNotAllowToInstantiateViaConstructor()
    {
        $reflectionClass = new \ReflectionClass('Fumocker\CallbackRegistry');

        $reflectionConstructor = $reflectionClass->getConstructor();
        $this->assertInstanceOf('ReflectionMethod', $reflectionConstructor, 'Constructor method should be defined in the class');
        $this->assertFalse($reflectionConstructor->isPublic(), 'The constructor method should not have public access');
    }

    /**
     * @test
     */
    public function shouldNotAllowToClone()
    {
        $reflectionClass = new \ReflectionClass('Fumocker\CallbackRegistry');

        $reflectionClone = $reflectionClass->getMethod('__clone');
        $this->assertInstanceOf('ReflectionMethod', $reflectionClone, 'Clone method should be defined in the class');
        $this->assertFalse($reflectionClone->isPublic(), 'The clone method should not have public access');
    }

    /**
     * @test
     */
    public function shouldAllowToGetSingletonInstanceOfRegistry()
    {
        $registry = CallbackRegistry::getInstance();

        $this->assertInstanceOf('Fumocker\CallbackRegistry', $registry);
    }

    /**
     * @test
     */
    public function shouldAlwaysReturnTheSameInstance()
    {
        $registryOne = CallbackRegistry::getInstance();
        $registryTwo = CallbackRegistry::getInstance();

        $this->assertSame($registryOne, $registryTwo);
    }

    /**
     * @static
     *
     * @return array
     */
    public static function provideNoStrings()
    {
        return array(
            array(null),
            array(true),
            array(false),
            array(new \stdClass()),
            array(function() {}),
            array(-10),
            array(0),
            array(10),
            array(1.1),
        );
    }

    /**
     * @static
     *
     * @return array
     */
    public static function provideValidIdentifiers()
    {
        return array(
            array('a'),
            array('a1'),
            array(''),
            array('  '),
        );
    }

    /**
     * @static
     *
     * @return array
     */
    public static function provideValidCallbacks()
    {
        $staticMethod = array(__NAMESPACE__.'\StubMethodCall', 'staticMethod');
        $objectMethod = array(new StubMethodCall(), 'objectMethod');
        $closure = function() {};
        $function = 'is_callable';

        return array(
            array($staticMethod),
            array($objectMethod),
            array($closure),
            array($function),
        );
    }

    /**
     * @static
     *
     * @return array
     */
    public static function provideNoCallableItems()
    {
        return array(
            array('string'),
            array(1),
            array(12.2),
            array(array()),
            array(false),
            array(null),
            array(new \stdClass()),
            array(array(new \stdClass(), 'no_exist_method')),
            array(array('stdClass', 'no_exist_method')),
        );
    }
}

class StubMethodCall
{
  public static function staticMethod() {}

  public function objectMethod() {}
}