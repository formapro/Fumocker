<?php

namespace Fumocker;

class Proxy
{
    /**
     *
     * @var string
     */
    protected $functionName;

    /**
     *
     * @var string
     */
    protected $namespace;

    /**
     * @var Callable
     */
    protected $callback;

    /**
     *
     * @param string $functionName
     * @param string $namespace
     */
    public function __construct($functionName, $namespace)
    {
        if (false == is_string($functionName)) {
            throw new \InvalidArgumentException(sprintf('Invalid function name provided. should be string but `%s` provided', gettype($functionName)));
        }
        if (empty($functionName)) {
            throw new \InvalidArgumentException('Function name is empty');
        }
        if (false == \function_exists($functionName)) {
            throw new \LogicException(sprintf('Function `%s` does not exist', $functionName));
        }
        $this->functionName = $functionName;

        if (false == is_string($namespace)) {
            throw new \InvalidArgumentException(sprintf('Invalid namespace provided. should be string but `%s` provided', gettype($namespace)));
        }
        if (empty($namespace)) {
            throw new \LogicException('Namespace is empty. It is not possible create the function in global namespace. There is original one');
        }
        $this->namespace = $namespace;
    }

    public function setCallback($callback)
    {
        if (false == is_callable($callback)) {
            throw new \InvalidArgumentException('Invalid callback provided');
        }

        $this->callback = $callback;
    }

    public function call()
    {
        return call_user_func_array($this->callback ?: $this->functionName, func_get_args());
    }

    public function getFunctionName()
    {
        return $this->functionName;
    }

    public function getNamespace()
    {
        return $this->namespace;
    }
}
