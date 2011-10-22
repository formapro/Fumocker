<?php
namespace Fumocker;

class Generator
{
    /**
     * @throws \LogicException if the function has already been created by a user in the given namespace
     * @throws \LogicException if the function has already been mocked in the given namespace
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

        $code =
"
namespace {$proxy->getNamespace()};

const {$this->generateConstant($proxy->getFunctionName())} = '{$identifier}';

function {$proxy->getFunctionName()}()
{
    \$callable = \\Fumocker\\CallbackRegistry::getInstance()->get('{$identifier}');

    return \\call_user_func_array(\$callable, \\func_get_args());
}
";
        eval($code);

        return $identifier;
    }

    /**
     * @param Proxy $proxy
     *
     * @return bool
     */
    public function isMocked(Proxy $proxy)
    {
        return defined($proxy->getNamespace(). '\\' . $this->generateConstant($proxy->getFunctionName()));
    }

    /**
     * @param Proxy $proxy
     *
     * @return string
     */
    protected function generateConstant($functionName)
    {
        return '__FUMOCKER_'.strtoupper($functionName);
    }
}