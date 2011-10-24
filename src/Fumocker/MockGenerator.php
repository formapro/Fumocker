<?php
namespace Fumocker;

class MockGenerator
{
    /**
     * @throws \LogicException if the function has already been created by a user in the given namespace
     * @throws \LogicException if the function has already been mocked in the given namespace
     *
     * @param string $functionName
     * @param string $namespace
     *
     * @return void
     */
    public function generate($functionName, $namespace)
    {
        if (\function_exists("$namespace\\$functionName") && false == $this->hasGenerated($functionName, $namespace)) {
            throw new \LogicException(sprintf(
                'The function `%s` in the namespace `%s` has already been defined by a user',
                $functionName,
                $namespace
            ));
        }
        if ($this->hasGenerated($functionName, $namespace)) {
            throw new \LogicException(sprintf(
                'The function `%s` in the namespace `%s` has been already mocked',
                $functionName,
                $namespace
            ));
        }

        $constantName = $this->generateConstantName($functionName);

        $code =
"
namespace {$namespace};

const {$constantName} = 1;

function {$functionName}()
{
    \$callable = \\Fumocker\\CallbackRegistry::getInstance()->get('{$namespace}', '{$functionName}');

    return \\call_user_func_array(\$callable, \\func_get_args());
}
";
        eval($code);
    }

    /**
     * @param Proxy $proxy
     *
     * @return bool
     */
    public function hasGenerated($functionName, $namespace)
    {
        return defined($namespace . '\\' . $this->generateConstantName($functionName));
    }

    /**
     * @param Proxy $proxy
     *
     * @return string
     */
    protected function generateConstantName($functionName)
    {
        return '__FUMOCKER_'.strtoupper($functionName);
    }
}