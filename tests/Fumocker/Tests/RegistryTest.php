<?php
namespace Fumocker\Tests;

class RegistryTest extends \PHPUnit_Framework_TestCase
{
    public function testClassExists()
    {
        $this->assertTrue(class_exists('Fumocker\Registry'));
    }
}