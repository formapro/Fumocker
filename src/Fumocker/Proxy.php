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
        $this->setFunctionName($functionName);
        $this->setNamespace($namespace);
    }

    protected function setFunctionName($functionName)
    {
        if (false == \is_string($functionName)) {
            throw new \InvalidArgumentException(
                \sprintf('Invalid function name provided. should be string but `%s` provided', \gettype($functionName)));
        }
        $functionName = \trim($functionName);
        if (empty($functionName)) {
            throw new \InvalidArgumentException('Function name is empty');
        }

        $this->functionName = $functionName;
    }

    protected function setNamespace($namespace)
    {
        if (false == \is_string($namespace)) {
            throw new \InvalidArgumentException(
                \sprintf('Invalid namespace provided. should be string but `%s` provided', \gettype($namespace)));
        }
        $namespace = \trim($namespace);
        if (empty($namespace)) {
            throw new \LogicException(
                'Namespace is empty. It is not possible create the function in global namespace. There is original one');
        }

        $this->namespace = $namespace;
    }

    /**
     * @throws \InvalidArgumentException
     * @param $callback
     *
     * @return void
     */
    public function setCallback($callback)
    {
        if (false == \is_callable($callback)) {
            throw new \InvalidArgumentException('Invalid callback provided');
        }

        $this->callback = $callback;
    }

    /**
     * @return mixed
     */
    public function call()
    {
        if ($this->callback) {
            return \call_user_func_array($this->callback, \func_get_args());
        }

        if (false == \function_exists($this->functionName)) {
            throw new \BadFunctionCallException(sprintf('The function `%s` is not exist in global namespace', $this->functionName));
        }

        return \call_user_func_array($this->functionName, \func_get_args());
    }

    /**
     * @return string
     */
    public function getFunctionName()
    {
        return $this->functionName;
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }
}