<?php

namespace Fumocker;

class Registry
{
    /**
     * @var Registry
     */
    protected static $instance;

    /**
     *
     */
    protected function __construct()
    {

    }

    /**
     * @return void
     */
    protected function __clone()
    {

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
