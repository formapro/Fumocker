<?php
namespace Fumocker\Tests;

use Fumocker\Generator;
use Fumocker\Proxy;
use Fumocker\CallbackRegistry;

class GeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldAllowToCheckWhetherFunctionMocked()
    {
        $generator = new Generator();

        $this->assertTrue($generator->isMocked('mocked_function', __NAMESPACE__), 'Should be mocked function');
    }

    /**
     * @test
     */
    public function shouldAllowToCheckWhetherFunctionMockedOrUserDefined()
    {
        $generator = new Generator();

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
        $generator = new Generator();

        $generator->generate(new Proxy('user_defined_function', __NAMESPACE__));
    }

    /**
     * @test
     *
     * @expectedException LogicException
     * @expectedExceptionMessage The function `mocked_function` in the namespace `Fumocker\Tests` has been already mocked
     */
    public function throwIfMockedFunctionAlreadyGeneratedInTheNamespace()
    {
        $generator = new Generator();

        $generator->generate(new Proxy('mocked_function', __NAMESPACE__));
    }

    /**
     * @test
     */
    public function shouldGenerateMockedFunction()
    {
        //guard
        $this->assertFunctionNotExists('test_generate_function_mock', __NAMESPACE__);

        $proxy = new Proxy('test_generate_function_mock', __NAMESPACE__);

        $generator = new Generator();

        $generator->generate($proxy);

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

        $proxyOne = new Proxy('test_unique_identifier_one', __NAMESPACE__);
        $proxyTwo = new Proxy('test_unique_identifier_two', __NAMESPACE__);

        $generator = new Generator();

        $identifierOne = $generator->generate($proxyOne);
        $identifierTwo = $generator->generate($proxyTwo);

        $this->assertInternalType('string', $identifierOne);
        $this->assertInternalType('string', $identifierTwo);

        $this->assertNotEmpty($identifierOne);
        $this->assertNotEmpty($identifierTwo);

        $this->assertNotEquals($identifierOne, $identifierTwo);
    }

    /**
     * @test
     */
    public function shouldSetIdentifierToMockedFunctionConstantWhileGeneratingAMock()
    {
        //guard
        $this->assertFunctionNotExists('test_set_identifier', __NAMESPACE__);

        $proxy = new Proxy('test_set_identifier', __NAMESPACE__);
        $proxy->setCallback(function() {});

        $generator = new Generator();

        $expectedIdentifier = $generator->generate($proxy);

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
        $this->assertFunctionNotExists('test_redirect_call_to_proxy', __NAMESPACE__);

        $mockCallable = $this->getMock('\stdClass', array('__invoke'));
        $mockCallable
            ->expects($this->once())
            ->method('__invoke')
        ;

        $proxy = new Proxy('test_redirect_call_to_proxy', __NAMESPACE__);

        $generator = new Generator();

        $identifier = $generator->generate($proxy);
        CallbackRegistry::getInstance()->set($identifier, $mockCallable);

        $this->assertFunctionExists('test_redirect_call_to_proxy', __NAMESPACE__);

        test_redirect_call_to_proxy();
    }

    /**
     * @test
     */
    public function shouldProxyMockedFunctionArgumentsToAProxy()
    {
        //guard
        $this->assertFunctionNotExists('test_proxy_arguments_proxy', __NAMESPACE__);

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

        $proxy = new Proxy('test_proxy_arguments_proxy', __NAMESPACE__);

        $generator = new Generator();

        $identifier = $generator->generate($proxy);
        CallbackRegistry::getInstance()->set($identifier, $mockCallable);

        $this->assertFunctionExists('test_proxy_arguments_proxy', __NAMESPACE__);

        test_proxy_arguments_proxy($expectedFirstArgument, $expectedSecondArgument, $expectedThirdArgument);
    }

    /**
     * @test
     */
    public function shouldReturnProxyResultAsMockedFunction()
    {
        //guard
        $this->assertFunctionNotExists('test_return_proxy_result', __NAMESPACE__);

        $expectedResult = 'foo';

        $mockCallable = $this->getMock('\stdClass', array('__invoke'));
        $mockCallable
            ->expects($this->once())
            ->method('__invoke')
            ->will($this->returnValue($expectedResult))
        ;

        $proxy = new Proxy('test_return_proxy_result', __NAMESPACE__);

        $generator = new Generator();

        $identifier = $generator->generate($proxy);
        CallbackRegistry::getInstance()->set($identifier, $mockCallable);

        $this->assertFunctionExists('test_return_proxy_result', __NAMESPACE__);

        $this->assertEquals($expectedResult, test_return_proxy_result());
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
