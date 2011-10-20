<?php
namespace Fumocker\Tests;

use Fumocker\Proxy;

class ProxyTest extends \PHPUnit_Framework_TestCase
{
    public static function provideNotStringTypes()
    {
        return array(
            array(123),
            array(new \stdClass()),
            array(array()),
            array(null),
        );
    }

    /**
     *
     * @test
     */
    public function shouldTakeNamespaceAndFunctionNameInConstructor()
    {
        new Proxy('str_replace', 'Foo\Bar');
    }

    /**
     *
     * @test
     *
     * @dataProvider provideNotStringTypes
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid function name provided
     */
    public function throwInvalidIfFunctionNameNotString($invalidFunctionName)
    {
        new Proxy($invalidFunctionName, 'Foo\Bar');
    }

    /**
     *
     * @test
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Function name is empty
     */
    public function throwInvalidIfFunctionNameEmpty()
    {
        new Proxy('', 'Foo\Bar');
    }

    /**
     *
     * @test
     *
     * @dataProvider provideNotStringTypes
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid namespace provided
     */
    public function throwInvalidIfNamespaceNotString($invalidNamespace)
    {
        new Proxy('str_replace', $invalidNamespace);
    }

    /**
     *
     * @test
     *
     * @expectedException LogicException
     * @expectedExceptionMessage Namespace is empty. It is not possible create the function in global namespace
     */
    public function throwIfNamespaceEmpty()
    {
        new Proxy('str_replace', '');
    }

    /**
     *
     * @test
     *
     * @expectedException LogicException
     * @expectedExceptionMessage Function `foo` does not exist
     */
    public function throwIfNotExistFunctionProvided()
    {
        new Proxy('foo', 'Foo\Bar');
    }


    /**
     *
     * @test
     */
    public function shouldAllowToGetFunctionNameSetInConstructor()
    {
        $expectedFunction = 'str_replace';

        $proxy = new Proxy($expectedFunction, 'Foo\Bar');

        $this->assertEquals($expectedFunction, $proxy->getFunctionName());
    }

    /**
     *
     * @test
     */
    public function shouldAllowToGetNamespaceSetInConstructor()
    {
        $expectedNamespace = 'Foo\Bar';

        $proxy = new Proxy('str_replace', $expectedNamespace);

        $this->assertEquals($expectedNamespace, $proxy->getNamespace());
    }

    /**
     *
     * @test
     */
    public function shouldCallOriginalFunction()
    {
        $proxy = new Proxy('str_replace', 'Foo\Bar');

        $result = $proxy->call('John', 'Joe', 'Hello John');

        $this->assertEquals('Hello Joe', $result);
    }

    /**
     *
     * @test
     */
    public function shouldAllowToSetCustomFunctionCallback()
    {
        $proxy = new Proxy('str_replace', 'Foo\Bar');

        $proxy->setCallback(function() {});
    }

    /**
     *
     * @test
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid callback provided
     */
    public function throwIfInvalidCallbackProvided()
    {
        $proxy = new Proxy('str_replace', 'Foo\Bar');

        $proxy->setCallback('invalid-callback');
    }

    /**
     *
     * @test
     */
    public function shouldProxyCallToCallbackIfDefined()
    {
        $called = false;

        $proxy = new Proxy('str_replace', 'Foo\Bar');
        $proxy->setCallback(function() use (&$called) {
            $called = true;
        });

        $proxy->call('foo', 'bar', 'ffooo');

        $this->assertTrue($called, 'The callback was not called. It should be called instead of original function');
    }

    /**
     *
     * @test
     */
    public function shouldProxyCallToCallbackWithProvidedArguments()
    {
        $expectedFirstArgument = 'foo';
        $expectedSecondArgument = 'bar';
        $expectedThirdArgument = 'ffooo';

        $mock = $this->getMock('stdClass', array('callback'));
        $mock->expects($this->once())->method('callback')->with(
            $this->equalTo($expectedFirstArgument),
            $this->equalTo($expectedSecondArgument),
            $this->equalTo($expectedThirdArgument));

        $proxy = new Proxy('str_replace', 'Foo\Bar');
        $proxy->setCallback(array($mock, 'callback'));

        $proxy->call($expectedFirstArgument, $expectedSecondArgument, $expectedThirdArgument);
    }

    /**
     *
     * @test
     */
    public function shouldCallToCallbackAndProxyItsReturn()
    {
        $expectedResult = 'calculated-result';

        $proxy = new Proxy('str_replace', 'Foo\Bar');
        $proxy->setCallback(function() use($expectedResult) {
            return $expectedResult;
        });

        $actualResult = $proxy->call();

        $this->assertEquals($expectedResult, $actualResult);
    }
}