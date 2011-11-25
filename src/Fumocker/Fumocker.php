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
     * @param MockGenerator $generator
     */
    public function __construct(MockGenerator $generator, CallbackRegistry $registry)
    {
        $this->generator = $generator;
        $this->registry = $registry;
    }

    /**
     * @param string $namespace
     * @param string $function
     * @param callable $callable
     *
     * @throws \InvalidArgumentException if function does not exist in global namespace
     *
     * @return void
     */
    public function set($namespace, $function, $callable)
    {
        if (false == \function_exists($function)) {
            throw new \InvalidArgumentException(\sprintf(
                'The global function with name `%s` does not exist.',
                $function
            ));
        }

        if (false == $this->generator->hasGenerated($namespace, $function)) {
            $this->generator->generate($namespace, $function);
        }

        $this->registry->set($namespace, $function, $callable);
    }

    /**
     * This function sets a function in global namespace as a callable for all mocked functions
     * Can be used to revert all changes
     *
     * @return void
     */
    public function setGlobals()
    {
        foreach ($this->registry->getAll() as $data) {
            $this->registry->set($data['namespace'], $data['function'], $data['function']);
        }
    }
}