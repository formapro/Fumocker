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
    public function shouldRequireRegistryToBeProvidedInConstructor()
    {
        new Generator($this->createRegistryStub());
    }

    /**
     * @test
     */
    public function shouldAllowToCheckWhetherFunctionMocked()
    {
        $generator = new Generator($this->createRegistryStub());
        $proxy = new Proxy('mocked_function', __NAMESPACE__);

        $this->assertTrue($generator->isMocked($proxy), 'Should be mocked function');
    }

    /**
     * @test
     */
    public function shouldAllowToCheckWhetherFunctionMockedOrUserDefined()
    {
        $generator = new Generator($this->createRegistryStub());
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
        $generator = new Generator($this->createRegistryStub());

        $generator->generate(new Proxy('user_defined_function', __NAMESPACE__));
    }

    /**
     * @test
     *
     * @expectedException LogicException
     * @expectedExceptionMessage The function `mocked_function` in the namespace `Fumocker\Tests` has been already mocked
     */
    public function throwIfMockedFunctionAlreadyExistInTheNamespace()
    {
        $generator = new Generator($this->createRegistryStub());

        $generator->generate(new Proxy('mocked_function', __NAMESPACE__));
    }

    /**
     * @test
     */
    public function shouldGenerateMockedFunction()
    {
        //guard
        $this->assertFalse(function_exists(__NAMESPACE__ . '\\' . 'test_generate_function_mock'));

        $proxy = new Proxy('test_generate_function_mock', __NAMESPACE__);

        $generator = new Generator($this->createRegistryStub());

        $generator->generate($proxy);

        $this->assertTrue(function_exists(__NAMESPACE__ . '\\' . 'test_generate_function_mock'));
        $this->assertTrue($generator->isMocked($proxy));
    }

    /**
     * @test
     */
    public function shouldSetProxyToARegistryWithIdentifierWhileGeneratingAMock()
    {
        //guard
        $this->assertFalse(function_exists(__NAMESPACE__ . '\\' . 'test_set_to_registry'));

        $proxy = new Proxy('test_set_to_registry', __NAMESPACE__);

        $registry = $this->getMock('Fumocker\Registry', array('setProxy'), array(), '', false);
        $registry
            ->expects($this->once())
            ->method('setProxy')
            ->with(
                $this->equalTo(spl_object_hash($proxy)),
                $this->equalTo($proxy)
            )
        ;

        $generator = new Generator($registry);

        $generator->generate($proxy);
    }

    /**
     * @test
     */
    public function shouldSetIdentifierToMockedFunctionConstantWhileGeneratingAMock()
    {
        //guard
        $this->assertFalse(function_exists(__NAMESPACE__ . '\\' . 'test_set_identifier'));

        $proxy = new Proxy('test_set_identifier', __NAMESPACE__);
        $proxy->setCallback(function() {});

        $generator = new Generator(Registry::getInstance());

        $generator->generate($proxy);

        $mockedFunctionConstant = __NAMESPACE__ . '\\' . '__FUMOCKER_TEST_SET_IDENTIFIER';
        $this->assertTrue(defined($mockedFunctionConstant));
        $this->assertEquals(spl_object_hash($proxy), constant($mockedFunctionConstant));
    }

    /**
     * @test
     */
    public function shouldRedirectMockedFunctionCallToAProxy()
    {
        //guard
        $this->assertFalse(function_exists(__NAMESPACE__ . '\\' . 'test_redirect_call_to_proxy'));

        $proxy = $this->getMock(
            'Fumocker\Proxy', array('call'), array('test_redirect_call_to_proxy', __NAMESPACE__));

        $proxy
            ->expects($this->once())
            ->method('call');

        $generator = new Generator(Registry::getInstance());

        $generator->generate($proxy);

        $this->assertTrue(function_exists(__NAMESPACE__ . '\\' . 'test_redirect_call_to_proxy'));

        test_redirect_call_to_proxy();
    }

    /**
     * @test
     */
    public function shouldProxyMockedFunctionArgumentsToAProxy()
    {
        //guard
        $this->assertFalse(function_exists(__NAMESPACE__ . '\\' . 'test_proxy_arguments_proxy'));

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


        $generator = new Generator(Registry::getInstance());

        $generator->generate($proxy);

        $this->assertTrue(function_exists(__NAMESPACE__ . '\\' . 'test_proxy_arguments_proxy'));

        test_proxy_arguments_proxy($expectedFirstArgument, $expectedSecondArgument, $expectedThirdArgument);
    }

    /**
     * @test
     */
    public function shouldReturnProxyResultAsMockedFunction()
    {
        //guard
        $this->assertFalse(function_exists(__NAMESPACE__ . '\\' . 'test_return_proxy_result'));

        $proxy = $this->getMock(
            'Fumocker\Proxy', array('call'), array('test_return_proxy_result', __NAMESPACE__));

        $excpectedResult = 'foo';

        $proxy
            ->expects($this->once())
            ->method('call')
            ->will($this->returnValue($excpectedResult))
        ;

        $generator = new Generator(Registry::getInstance());

        $generator->generate($proxy);

        $this->assertTrue(function_exists(__NAMESPACE__ . '\\' . 'test_return_proxy_result'));

        $this->assertEquals($excpectedResult, test_return_proxy_result());
    }

    /**
     * @return \Fumocker\Registry
     */
    protected function createRegistryStub()
    {
        return $this->getMock('Fumocker\Registry', array(), array(), '', false);
    }
}

function user_defined_function()
{

}

const __FUMOCKER_MOCKED_FUNCTION = 1;

function mocked_function()
{

}
