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
     * @static
     *
     * @return array
     */
    public static function provideInvalidIdentifiers()
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
        $static_method = array(__NAMESPACE__.'\StubMethodCall', 'staticMethod');
        $object_method = array(new StubMethodCall(), 'objectMethod');
        $closure = function() {};
        $function = 'is_callable';

        return array(
            array($static_method),
            array($object_method),
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

    /**
     * @test
     *
     * @dataProvider provideValidCallbacks
     */
    public function shouldAllowToSetCallable($validCallable)
    {
        CallbackRegistry::getInstance()->set('an_id', $validCallable);
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
        CallbackRegistry::getInstance()->set('an_id', $invalidCallable);
    }

    /**
     * @test
     *
     * @dataProvider provideInvalidIdentifiers
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid identifier provided, Should be not empty string
     */
    public function throwWhenSetCallableWithInvalidIdentifier($invalidIdentifier)
    {
        $registry = CallbackRegistry::getInstance();

        $registry->set($invalidIdentifier, function(){});
    }

    /**
     * @test
     */
    public function shouldAllowToGetCallableByIdentifier()
    {
        $identifier = 'an_id';
        $expectedCallable = function(){};

        $registry = CallbackRegistry::getInstance();
        $registry->set($identifier, $expectedCallable);

        $actualCallable = $registry->get($identifier);

        $this->assertSame($expectedCallable, $actualCallable);
    }

    /**
     * @test
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid identifier `not_set_callable` given. Cannot find a callable related to it.
     */
    public function throwWhenGetCallableNotSetBefore()
    {
        $registry = CallbackRegistry::getInstance();

        $registry->get('not_set_callable');
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
}

class StubMethodCall
{
  public static function staticMethod() {}

  public function objectMethod() {}
}