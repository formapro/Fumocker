<?php
namespace Fumocker;

class Generator
{
    protected $registry;

    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @throws \LogicException
     *
     * @param Proxy $proxy
     *
     * @return void
     */
    public function generate(Proxy $proxy)
    {
        if (\function_exists($proxy->getNamespace() . '\\' . $proxy->getFunctionName()) && false == $this->isMocked($proxy)) {
            throw new \LogicException(sprintf(
                'The function `%s` in the namespace `%s` has already been defined by a user',
                $proxy->getFunctionName(), $proxy->getNamespace()));
        }
        if ($this->isMocked($proxy)) {
            throw new \LogicException(sprintf(
                'The function `%s` in the namespace `%s` has been already mocked',
                $proxy->getFunctionName(),$proxy->getNamespace()));
        }

        $identifier = spl_object_hash($proxy);
        $this->registry->setProxy($identifier, $proxy);

        $code =
"
namespace {$proxy->getNamespace()};

use Fumocker\Registry as FumockerRegistry;

const {$this->generateConstant($proxy)} = '{$identifier}';

function {$proxy->getFunctionName()}()
{
    \$proxy = FumockerRegistry::getInstance()->getProxy('{$identifier}');

    return \\call_user_func_array(array(\$proxy, 'call'), \\func_get_args());
}
";
        eval($code);
    }

    /**
     * @param Proxy $proxy
     *
     * @return bool
     */
    public function isMocked(Proxy $proxy)
    {
        return defined($proxy->getNamespace(). '\\' . $this->generateConstant($proxy));
    }

    /**
     * @param Proxy $proxy
     *
     * @return string
     */
    protected function generateConstant(Proxy $proxy)
    {
        return '__FUMOCKER_'.strtoupper($proxy->getFunctionName());
    }
}