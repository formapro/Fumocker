<?php
namespace Fumocker;

class Fumocker
{
    /**
     * @var \Fumocker\MockGenerator
     */
    protected $generator;

    /**
     * @var \Fumocker\CallbackRegistry
     */
    protected $registry;

    /**
     * @var array
     */
    protected $mocks = array();

    /**
     * @param MockGenerator $generator
     */
    public function __construct(MockGenerator $generator = null, CallbackRegistry $registry = null)
    {
        $this->generator = $generator ?: new MockGenerator();
        $this->registry = $registry ?: CallbackRegistry::getInstance();
    }

    /**
     * @param string $namespace
     * @param string $function
     * @param callable $callable
     *
     * @throws \InvalidArgumentException if function does not exist in global namespace
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    public function getMock($namespace, $function)
    {
        if (false == \class_exists('PHPUnit_Framework_MockObject_Generator', true)) {
            throw new \RuntimeException(
                'PHPUnit_Framework_MockObject_Generator class not found. In order to use this library install PHPUnit_Framework_MockObject library.'
            );
        }

        if (false == \function_exists($function)) {
            throw new \InvalidArgumentException(\sprintf(
                'The global function with name `%s` does not exist.',
                $function
            ));
        }

        if (false == $this->generator->hasGenerated($namespace, $function)) {
            $this->generator->generate($namespace, $function);
        }

        $generator = new \PHPUnit_Framework_MockObject_Generator;
        $this->mocks[] = $functionMock = $generator->getMock('stdClass', array($function));

        $this->registry->set($namespace, $function, array($functionMock, $function));

        return $functionMock;
    }

    /**
     * @return void
     */
    public function cleanup()
    {
        foreach ($this->registry->getAll() as $data) {
            $this->registry->set($data['namespace'], $data['function'], $data['function']);
        }

        $mocks = $this->mocks;
        $this->mocks = array();
        foreach ($mocks as $mock) {
            $mock->__phpunit_verify();
        }
    }
}