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

    public static function provideEmpties()
    {
        return array(
            array(''),
            array('  '),
        );
    }

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
     * @dataProvider provideEmpties
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Function name is empty
     */
    public function throwInvalidIfFunctionNameEmpty($emptyFunctionName)
    {
        new Proxy($emptyFunctionName, 'Foo\Bar');
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
     * @dataProvider provideEmpties
     *
     * @expectedException LogicException
     * @expectedExceptionMessage Namespace is empty. It is not possible create the function in global namespace
     */
    public function throwIfNamespaceEmpty($emptyNamespace)
    {
        new Proxy('str_replace', $emptyNamespace);
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
     *
     * @expectedException BadFunctionCallException
     * @expectedExceptionMessage The function `not_exist_function` is not exist in global namespace
     */
    public function throwOnCallIfOriginalFunctionNotExistAndCallbackNotSet()
    {
        $proxy = new Proxy('not_exist_function', 'Foo\Bar');

        $proxy->call();
    }

    /**
     *
     * @test
     */
    public function shouldCallOriginalIfCallbackIsNotSet()
    {
        $proxy = new Proxy('str_replace', 'Foo\Bar');

        $result = $proxy->call('John', 'Joe', 'Hello John');

        $this->assertEquals('Hello Joe', $result);
    }

    /**
     *
     * @test
     *
     * @dataProvider provideValidCallbacks
     */
    public function shouldAllowToSetCustomCallback($validCallback)
    {
        $proxy = new Proxy('str_replace', 'Foo\Bar');

        $proxy->setCallback($validCallback);
    }

    /**
     *
     * @test
     *
     * @dataProvider provideNoCallableItems
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid callback provided
     */
    public function throwIfInvalidCallbackProvided($invalidCallback)
    {
        $proxy = new Proxy('str_replace', 'Foo\Bar');

        $proxy->setCallback($invalidCallback);
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
        $mock
            ->expects($this->once())
            ->method('callback')
            ->with(
                $this->equalTo($expectedFirstArgument),
                $this->equalTo($expectedSecondArgument),
                $this->equalTo($expectedThirdArgument)
            )
        ;

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

class StubMethodCall
{
  public static function staticMethod() {}

  public function objectMethod() {}
}