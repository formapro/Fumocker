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
     * @test
     *
     * @dataProvider provideValidIdentifiers
     */
    public function shouldAllowToSetProxyWithIdentifier($validIdentifier)
    {
        $proxy = $this->getMock('Fumocker\\Proxy', array(), array(), '', false);

        $registry = CallbackRegistry::getInstance();

        $registry->setProxy($validIdentifier, $proxy);
    }

    /**
     * @test
     *
     * @dataProvider provideInvalidIdentifiers
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid identifier provided, Should be not empty string
     */
    public function throwIfInvalidIdentifierProvidedWhileSettingAProxy($invalidIdentifier)
    {
        $proxy = $this->getMock('Fumocker\\Proxy', array(), array(), '', false);

        $registry = CallbackRegistry::getInstance();

        $registry->setProxy($invalidIdentifier, $proxy);
    }

    /**
     * @test
     */
    public function shouldAllowToGetProxyByIdentifier()
    {
        $identifier = 'an_id';
        $expectedProxy = $this->getMock('Fumocker\\Proxy', array(), array(), '', false);

        $registry = CallbackRegistry::getInstance();

        $registry->setProxy($identifier, $expectedProxy);

        $actualProxy = $registry->get($identifier);

        $this->assertSame($expectedProxy, $actualProxy);
    }

    /**
     * @test
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMesssage Invalid identifier `not_set_proxy` given. Cannot find a proxy related to it.
     */
    public function throwIfProxyWithGivenIdentifierNotExistInRegistry()
    {
        $registry = CallbackRegistry::getInstance();

        $registry->get('not_set_proxy');
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