<?php

namespace Fumocker;

class Registry
{
    /**
     * @var Registry
     */
    protected static $instance;

    protected $proxies;

    /**
     *
     */
    protected function __construct()
    {
        $this->proxies = array();
    }

    /**
     * @return void
     */
    protected function __clone()
    {

    }

    /**
     * @throws \InvalidArgumentException if identifier is not valid
     *
     * @param string $identifier
     * @param Proxy $proxy
     *
     * @return void
     */
    public function setProxy($identifier, Proxy $proxy)
    {
        if (false == is_string($identifier)) {
            throw new \InvalidArgumentException('Invalid identifier provided, Should be not empty string');
        }

        $this->proxies[$identifier] = $proxy;
    }

    /**
     * @throws \InvalidArgumentException if proxy for a given does not exist
     *
     * @param $identifier
     *
     * @return Proxy
     */
    public function getProxy($identifier)
    {
        if (false == isset($this->proxies[$identifier])) {
            throw new \InvalidArgumentException(
                \sprintf('Invalid identifier `%s` given. Cannot find a proxy related to it.', $identifier));
        }

        return $this->proxies[$identifier];
    }

    /**
     * @static
     *
     * @throws \RuntimeException
     *
     * @return Registry
     */
    public static function getInstance()
    {
        return self::$instance ?: self::$instance = new self();
    }
}