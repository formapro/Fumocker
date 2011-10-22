<?php
namespace Fumocker\Tests;

use Fumocker\Generator;
use Fumocker\Proxy;
use Fumocker\Registry;

class GeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldAllowToCheckWhetherFunctionMocked()
    {
        $generator = new Generator();
        $proxy = new Proxy('mocked_function', __NAMESPACE__);

        $this->assertTrue($generator->isMocked($proxy), 'Should be mocked function');
    }

    /**
     * @test
     */
    public function shouldAllowToCheckWhetherFunctionMockedOrUserDefined()
    {
        $generator = new Generator();
        $proxy = new Proxy('user_defined_function', __NAMESPACE__);

        $this->assertFalse($generator->isMocked($proxy), 'Should be user defined function');
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
        $this->assertTrue($generator->isMocked($proxy));
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
    public function shouldRedirectMockedFunctionCallToAProxy()
    {
        //guard
        $this->assertFunctionNotExists('test_redirect_call_to_proxy', __NAMESPACE__);

        $proxy = $this->getMock(
            'Fumocker\Proxy', array('call'), array('test_redirect_call_to_proxy', __NAMESPACE__));

        $proxy
            ->expects($this->once())
            ->method('call');

        $generator = new Generator();

        $identifier = $generator->generate($proxy);
        Registry::getInstance()->setProxy($identifier, $proxy);

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

        $proxy = $this->getMock(
            'Fumocker\Proxy', array('call'), array('test_proxy_arguments_proxy', __NAMESPACE__));

        $proxy
            ->expects($this->once())
            ->method('call')
            ->with(
                $this->equalTo($expectedFirstArgument),
                $this->equalTo($expectedSecondArgument),
                $this->equalTo($expectedThirdArgument)
            )
        ;


        $generator = new Generator();

        $identifier = $generator->generate($proxy);
        Registry::getInstance()->setProxy($identifier, $proxy);

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

        $proxy = $this->getMock(
            'Fumocker\Proxy', array('call'), array('test_return_proxy_result', __NAMESPACE__));

        $excpectedResult = 'foo';

        $proxy
            ->expects($this->once())
            ->method('call')
            ->will($this->returnValue($excpectedResult))
        ;

        $generator = new Generator();

        $identifier = $generator->generate($proxy);
        Registry::getInstance()->setProxy($identifier, $proxy);

        $this->assertFunctionExists('test_return_proxy_result', __NAMESPACE__);

        $this->assertEquals($excpectedResult, test_return_proxy_result());
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
