<?php
namespace Fumocker\Tests;

class GeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testClassExists()
    {
        $this->assertTrue(class_exists('Fumocker\Generator'));
    }
}