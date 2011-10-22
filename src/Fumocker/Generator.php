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
    public function generate($functionName, $namespace)
    {
        if (\function_exists("$namespace\\$functionName") && false == $this->isMocked($functionName, $namespace)) {
            throw new \LogicException(sprintf(
                'The function `%s` in the namespace `%s` has already been defined by a user',
                $functionName,
                $namespace
            ));
        }
        if ($this->isMocked($functionName, $namespace)) {
            throw new \LogicException(sprintf(
                'The function `%s` in the namespace `%s` has been already mocked',
                $functionName,
                $namespace
            ));
        }

        $constantName = $this->generateConstant($functionName);
        $identifier = "$namespace\\$functionName";

        $code =
"
namespace {$namespace};

const {$constantName} = '{$identifier}';

function {$functionName}()
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
    public function isMocked($functionName, $namespace)
    {
        return defined($namespace . '\\' . $this->generateConstant($functionName));
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