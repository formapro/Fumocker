<?php
namespace Fumocker;

/**
 *
 */
class MockGenerator
{
    /**
     * @throws \InvalidArgumentException if function not a string
     * @throws \InvalidArgumentException if function an empty string
     * @throws \InvalidArgumentException if namespace not a string
     * @throws \InvalidArgumentException if namespace an empty string
     * @throws \LogicException if the function has already been created by a user in the given namespace
     * @throws \LogicException if the function has already been mocked in the given namespace
     *
     * @param string $functionName
     * @param string $namespace
     *
     * @return void
     */
    public function generate($namespace, $functionName)
    {
        $this->throwInvalidFunctionName($functionName);
        $this->throwInvalidNamespace($namespace);
        $this->throwCanNotGenerateFunction($namespace, $functionName);

        $code =
"
namespace {$namespace};

const {$this->generateConstantName($functionName)} = true;

function {$functionName}()
{
    \$callable = \\Fumocker\\CallbackRegistry::getInstance()->get('{$namespace}', '{$functionName}');

    return \\call_user_func_array(\$callable, \\func_get_args());
}
";
        eval($code);
    }

    /**
     * @throws \InvalidArgumentException if function not a string
     * @throws \InvalidArgumentException if function an empty string
     *
     * @param string $functionName
     *
     * @return void
     */
    protected function throwInvalidFunctionName($functionName)
    {
        if (false == \is_string($functionName)) {
            throw new \InvalidArgumentException(
                \sprintf('Invalid function name provided. should be string but `%s` provided', \gettype($functionName)));
        }

        $functionName = \trim($functionName);
        if (empty($functionName)) {
            throw new \InvalidArgumentException('Given function name is empty');
        }
    }

    /**
     * @throws \InvalidArgumentException if namespace not a string
     * @throws \InvalidArgumentException if namespace an empty string
     *
     * @param string $namespace
     *
     * @return void
     */
    protected function throwInvalidNamespace($namespace)
    {
        if (false == \is_string($namespace)) {
            throw new \InvalidArgumentException(\sprintf(
                'Invalid namespace provided. should be string but `%s` provided',
                \gettype($namespace)
            ));
        }

        $namespace = \trim($namespace);
        if (empty($namespace)) {
            throw new \InvalidArgumentException('Given namespace is empty');
        }
    }

    /**
     * @throws \LogicException if function was created by user
     * @throws \LogicException if function has already been defined
     *
     * @param string $functionName
     * @param string $namespace
     *
     * @return void
     */
    protected function throwCanNotGenerateFunction($namespace, $functionName)
    {
        if (\function_exists("$namespace\\$functionName") && false == $this->hasGenerated($namespace, $functionName)) {
            throw new \LogicException(sprintf(
                'The function `%s` in the namespace `%s` has already been defined by a user',
                $functionName,
                $namespace
            ));
        }
        if ($this->hasGenerated($namespace, $functionName)) {
            throw new \LogicException(sprintf(
                'The function `%s` in the namespace `%s` has been already mocked',
                $functionName,
                $namespace
            ));
        }
    }

    /**
     * @param string $namespace
     * @param string $functionName
     *
     * @return bool
     */
    public function hasGenerated($namespace, $functionName)
    {
        return defined($namespace . '\\' . $this->generateConstantName($functionName));
    }

    /**
     * @param string $functionName
     *
     * @return string
     */
    protected function generateConstantName($functionName)
    {
        return '__FUMOCKER_'.strtoupper($functionName);
    }
}