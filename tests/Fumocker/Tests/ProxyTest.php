<?php
namespace Fumocker\Tests;

class ProxyTest extends \PHPUnit_Framework_TestCase
{
    public function testClassExists()
    {
        $this->assertTrue(class_exists('Fumocker\Proxy'));
    }
}