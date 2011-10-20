<?php
namespace Fumocker\Tests;

use Fumocker\Registry;

class RegistryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return void
     */
    public function setUp()
    {
        $classReflection = new \ReflectionClass('Fumocker\Registry');
        $propertyReflection = $classReflection->getProperty('instance');

        $propertyReflection->setAccessible(true);
        $propertyReflection->setValue($classReflection, null);
        $propertyReflection->setAccessible(false);
    }

    /**
     * @test
     */
    public function shouldNotAllowToInstantiateViaConstructor()
    {
        $reflectionClass = new \ReflectionClass('Fumocker\Registry');

        $reflectionConstructor = $reflectionClass->getConstructor();
        $this->assertInstanceOf('ReflectionMethod', $reflectionConstructor, 'Constructor method should be defined in the class');
        $this->assertFalse($reflectionConstructor->isPublic(), 'The constructor method should not have public access');
    }

    /**
     * @test
     */
    public function shouldNotAllowToClone()
    {
        $reflectionClass = new \ReflectionClass('Fumocker\Registry');

        $reflectionClone = $reflectionClass->getMethod('__clone');
        $this->assertInstanceOf('ReflectionMethod', $reflectionClone, 'Clone method should be defined in the class');
        $this->assertFalse($reflectionClone->isPublic(), 'The clone method should not have public access');
    }

    /**
     * @test
     */
    public function shouldAllowToGetSingletonInstanceOfRegistry()
    {
        $registry = Registry::getInstance();

        $this->assertInstanceOf('Fumocker\Registry', $registry);
    }

    /**
     * @test
     */
    public function shouldAlwaysReturnTheSameInstance()
    {
        $registryOne = Registry::getInstance();
        $registryTwo = Registry::getInstance();

        $this->assertSame($registryOne, $registryTwo);
    }
}