<?php
namespace Fumocker\Tests;

use Fumocker\MockGenerator;
use Fumocker\CallbackRegistry;

class MockGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     *
     * @dataProvider provideNotStringTypes
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid function name provided
     */
    public function throwWhenFunctionNameNotStringWhileGeneration($invalidFunctionName)
    {
        $generator = new MockGenerator();

        $generator->generate($invalidFunctionName, 'namespace');
    }

    /**
     * @test
     *
     * @dataProvider provideEmpties
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Given function name is empty
     */
    public function throwWhenFunctionEmptyWhileGeneration($emptyFunctionName)
    {
        $generator = new MockGenerator();

        $generator->generate($emptyFunctionName, 'namespace');
    }

    /**
     * @test
     *
     * @dataProvider provideNotStringTypes
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid namespace provided
     */
    public function throwWhenNamespaceNotStringWhileGeneration($invalidNamespace)
    {
        $generator = new MockGenerator();

        $generator->generate('function', $invalidNamespace);
    }

    /**
     * @test
     *
     * @dataProvider provideEmpties
     *
     * @expectedException LogicException
     * @expectedExceptionMessage Given namespace is empty
     */
    public function throwWhenNamespaceEmptyWhileGeneration($emptyNamespace)
    {
        $generator = new MockGenerator();

        $generator->generate('function', $emptyNamespace);
    }


    /**
     * @test
     */
    public function shouldAllowToCheckWhetherFunctionMocked()
    {
        $generator = new MockGenerator();

        $this->assertTrue($generator->hasGenerated('mocked_function', __NAMESPACE__), 'Should be mocked function');
    }

    /**
     * @test
     */
    public function shouldAllowToCheckWhetherFunctionMockedOrUserDefined()
    {
        $generator = new MockGenerator();

        $this->assertFalse($generator->hasGenerated('user_defined_function', __NAMESPACE__), 'Should be user defined function');
    }

    /**
     * @test
     *
     * @expectedException LogicException
     * @expectedExceptionMessage The function `user_defined_function` in the namespace `Fumocker\Tests` has already been defined by a user
     */
    public function throwIfUserAlreadyDefineFunctionInTheNamespace()
    {
        $generator = new MockGenerator();

        $generator->generate('user_defined_function', __NAMESPACE__);
    }

    /**
     * @test
     *
     * @expectedException LogicException
     * @expectedExceptionMessage The function `mocked_function` in the namespace `Fumocker\Tests` has been already mocked
     */
    public function throwIfMockedFunctionAlreadyGeneratedInTheNamespace()
    {
        $generator = new MockGenerator();

        $generator->generate('mocked_function', __NAMESPACE__);
    }

    /**
     * @test
     */
    public function shouldGenerateMockedFunction()
    {
        //guard
        $this->assertFunctionNotExists('test_generate_function_mock', __NAMESPACE__);

        $generator = new MockGenerator();

        $generator->generate('test_generate_function_mock', __NAMESPACE__);

        $this->assertFunctionExists('test_generate_function_mock', __NAMESPACE__);
        $this->assertTrue($generator->hasGenerated('test_generate_function_mock', __NAMESPACE__));
    }

    /**
     * @test
     */
    public function shouldGenerateConstantWhileGeneratingFunctionMock()
    {
        //guard
        $this->assertFunctionNotExists('test_set_identifier', __NAMESPACE__);

        $generator = new MockGenerator();

        $generator->generate('test_set_identifier', __NAMESPACE__);

        $mockedFunctionConstant = __NAMESPACE__ . '\\' . '__FUMOCKER_TEST_SET_IDENTIFIER';
        $this->assertTrue(defined($mockedFunctionConstant));
    }

    /**
     * @test
     */
    public function shouldRedirectMockedFunctionCallToAssignedCallable()
    {
        //guard
        $this->assertFunctionNotExists('test_redirect_call_to_callable', __NAMESPACE__);

        $mockCallable = $this->getMock('\stdClass', array('__invoke'));
        $mockCallable
            ->expects($this->once())
            ->method('__invoke')
        ;

        $generator = new MockGenerator();

        $generator->generate('test_redirect_call_to_callable', __NAMESPACE__);
        CallbackRegistry::getInstance()->set(__NAMESPACE__, 'test_redirect_call_to_callable', $mockCallable);

        $this->assertFunctionExists('test_redirect_call_to_callable', __NAMESPACE__);

        test_redirect_call_to_callable();
    }

    /**
     * @test
     */
    public function shouldProxyMockedFunctionArgumentsToCallable()
    {
        //guard
        $this->assertFunctionNotExists('test_proxy_arguments_to_callable', __NAMESPACE__);

        $expectedFirstArgument = 'foo';
        $expectedSecondArgument = array('bar');
        $expectedThirdArgument = new \stdClass();

        $mockCallable = $this->getMock('\stdClass', array('__invoke'));
        $mockCallable
            ->expects($this->once())
            ->method('__invoke')
            ->with(
                $this->equalTo($expectedFirstArgument),
                $this->equalTo($expectedSecondArgument),
                $this->equalTo($expectedThirdArgument)
            )
        ;

        $generator = new MockGenerator();

        $generator->generate('test_proxy_arguments_to_callable', __NAMESPACE__);
        CallbackRegistry::getInstance()->set(__NAMESPACE__, 'test_proxy_arguments_to_callable', $mockCallable);

        $this->assertFunctionExists('test_proxy_arguments_to_callable', __NAMESPACE__);

        test_proxy_arguments_to_callable($expectedFirstArgument, $expectedSecondArgument, $expectedThirdArgument);
    }

    /**
     * @test
     */
    public function shouldReturnCallableResultAsMockedFunction()
    {
        //guard
        $this->assertFunctionNotExists('test_return_callable_result', __NAMESPACE__);

        $expectedResult = 'foo';

        $mockCallable = $this->getMock('\stdClass', array('__invoke'));
        $mockCallable
            ->expects($this->once())
            ->method('__invoke')
            ->will($this->returnValue($expectedResult))
        ;

        $generator = new MockGenerator();

        $generator->generate('test_return_callable_result', __NAMESPACE__);
        CallbackRegistry::getInstance()->set(__NAMESPACE__, 'test_return_callable_result' ,$mockCallable);

        $this->assertFunctionExists('test_return_callable_result', __NAMESPACE__);

        $this->assertEquals($expectedResult, test_return_callable_result());
    }

    public function assertFunctionExists($functionName, $namesppace)
    {
        $this->assertTrue(function_exists($namesppace . '\\' . $functionName));
    }

    public function assertFunctionNotExists($functionName, $namesppace)
    {
        $this->assertFalse(function_exists($namesppace . '\\' . $functionName));
    }

    /**
     * @static
     *
     * @return array
     */
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
     * @static
     *
     * @return array
     */
    public static function provideEmpties()
    {
        return array(
            array(''),
            array('  '),
        );
    }
}

function user_defined_function()
{

}

const __FUMOCKER_MOCKED_FUNCTION = 1;

function mocked_function()
{

}
