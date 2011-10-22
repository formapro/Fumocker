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
    protected $callables;

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
     * @throws \InvalidArgumentException if identifier is not valid
     * @throws \InvalidArgumentException if invalid callable provided
     *
     * @param string $identifier
     * @param Callable $callable
     *
     * @return void
     */
    public function set($identifier, $callable)
    {
        if (false == is_string($identifier)) {
            throw new \InvalidArgumentException('Invalid identifier provided, Should be not empty string');
        }
        if (false == \is_callable($callable)) {
            throw new \InvalidArgumentException('Invalid callable provided');
        }

        $this->callables[$identifier] = $callable;
    }

    /**
     * @throws \InvalidArgumentException if callable for a given does not exist
     *
     * @param $identifier
     *
     * @return Callable
     */
    public function get($identifier)
    {
        if (false == isset($this->callables[$identifier])) {
            throw new \InvalidArgumentException(
                \sprintf('Invalid identifier `%s` given. Cannot find a callable related to it.', $identifier));
        }

        return $this->callables[$identifier];
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