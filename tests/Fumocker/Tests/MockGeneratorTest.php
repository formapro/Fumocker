<?php
namespace Fumocker\Tests;

use Fumocker\MockGenerator;
use Fumocker\CallbackRegistry;

class MockGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldAllowToCheckWhetherFunctionMocked()
    {
        $generator = new MockGenerator();

        $this->assertTrue($generator->isMocked('mocked_function', __NAMESPACE__), 'Should be mocked function');
    }

    /**
     * @test
     */
    public function shouldAllowToCheckWhetherFunctionMockedOrUserDefined()
    {
        $generator = new MockGenerator();

        $this->assertFalse($generator->isMocked('user_defined_function', __NAMESPACE__), 'Should be user defined function');
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
        $this->assertTrue($generator->isMocked('test_generate_function_mock', __NAMESPACE__));
    }

    /**
     * @test
     */
    public function shouldReturnUniqueIdentifierAssignedForMockedFunction()
    {
        //guard
        $this->assertFunctionNotExists('test_unique_identifier_one', __NAMESPACE__);
        $this->assertFunctionNotExists('test_unique_identifier_two', __NAMESPACE__);

        $generator = new MockGenerator();

        $identifierOne = $generator->generate('test_unique_identifier_one', __NAMESPACE__);
        $identifierTwo = $generator->generate('test_unique_identifier_two', __NAMESPACE__);

        $this->assertInternalType('string', $identifierOne);
        $this->assertInternalType('string', $identifierTwo);

        $this->assertNotEmpty($identifierOne);
        $this->assertNotEmpty($identifierTwo);

        $this->assertNotEquals($identifierOne, $identifierTwo);
    }

    /**
     * @test
     */
    public function shouldSetIdentifierToMockedFunctionConstantWhileGeneratingMock()
    {
        //guard
        $this->assertFunctionNotExists('test_set_identifier', __NAMESPACE__);

        $generator = new MockGenerator();

        $expectedIdentifier = $generator->generate('test_set_identifier', __NAMESPACE__);

        $mockedFunctionConstant = __NAMESPACE__ . '\\' . '__FUMOCKER_TEST_SET_IDENTIFIER';
        $this->assertTrue(defined($mockedFunctionConstant));

        $actualIdentifier = constant($mockedFunctionConstant);
        $this->assertEquals($expectedIdentifier, $actualIdentifier);
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

        $identifier = $generator->generate('test_redirect_call_to_callable', __NAMESPACE__);
        CallbackRegistry::getInstance()->set($identifier, $mockCallable);

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

        $identifier = $generator->generate('test_proxy_arguments_to_callable', __NAMESPACE__);
        CallbackRegistry::getInstance()->set($identifier, $mockCallable);

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

        $identifier = $generator->generate('test_return_callable_result', __NAMESPACE__);
        CallbackRegistry::getInstance()->set($identifier, $mockCallable);

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
}

function user_defined_function()
{

}

const __FUMOCKER_MOCKED_FUNCTION = 1;

function mocked_function()
{

}
