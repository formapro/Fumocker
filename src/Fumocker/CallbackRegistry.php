<?php

namespace Fumocker;

class CallbackRegistry
{
    /**
     * @var CallbackRegistry
     */
    protected static $instance;

    /**
     * @var array
     */
    protected $callables = array();

    protected function __construct()
    {
        $this->callables = array();
    }

    /**
     * @return void
     */
    protected function __clone()
    {

    }

    /**
     * @throws \InvalidArgumentException if function name is not valid
     * @throws \InvalidArgumentException if namespace is not valid
     * @throws \InvalidArgumentException if invalid callable provided
     *
     * @param string $functionName
     * @param string $namespace
     * @param Callable $callable
     *
     * @return void
     */
    public function set($namespace, $functionName, $callable)
    {
        if (false == is_string($functionName)) {
            throw new \InvalidArgumentException('Invalid function name provided. Should be a string');
        }
        if (false == is_string($namespace)) {
            throw new \InvalidArgumentException('Invalid namespace provided. Should be a string');
        }
        if (false == \is_callable($callable)) {
            throw new \InvalidArgumentException('Invalid callable provided');
        }

        $this->callables["$namespace\\$functionName"] = array(
            'namespace' => $namespace,
            'function' => $functionName,
            'callable' => $callable,
        );
    }

    /**
     * @throws \InvalidArgumentException if callable for a given namespace\function does not exist
     *
     * @param string $functionName
     * @param string $namespace
     *
     * @return Callable
     */
    public function get($namespace, $functionName)
    {
        if (false == isset($this->callables["$namespace\\$functionName"])) {
            throw new \InvalidArgumentException(
                \sprintf('Cannot find a callable related to %s()',
                "$namespace\\$functionName"
            ));
        }

        return $this->callables["$namespace\\$functionName"]['callable'];
    }

    public function getAll()
    {
        return array_values($this->callables);
    }

    /**
     * @static
     *
     * @return CallbackRegistry
     */
    public static function getInstance()
    {
        return self::$instance ?: self::$instance = new self();
    }
}