<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 05/07/2020 Vagner Cardoso
 */

namespace Core\Helpers;

/**
 * Class CallableResolver.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class CallableResolver
{
    /**
     * @var string
     */
    protected const CALLABLE_PATTERN = '!^([^\:]+)[\:|@]([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)$!';

    /**
     * @param callable|string $callable
     * @param mixed           $injectThis
     * @param string|object   $contract
     *
     * @return callable
     */
    public static function resolve($callable, $injectThis = null, $contract = null)
    {
        if (is_callable($callable)) {
            return $callable;
        }

        if (is_string($callable)) {
            $callable = self::resolveClassName($callable, $injectThis, $contract);
        } elseif ($callable instanceof \Closure) {
            /** @var \Closure $callable */
            $callable = $callable->bindTo($injectThis);
        }

        self::assertCallable($callable);

        return $callable;
    }

    /**
     * @param callable|string $callable
     * @param mixed           $injectThis
     * @param string|object   $contract
     *
     * @return array
     */
    protected static function resolveClassName($callable, $injectThis = null, $contract = null)
    {
        list($class, $method) = self::parseClassName($callable);

        if (!class_exists($class)) {
            throw new \RuntimeException(
                sprintf('Callable %s does not exist', $class)
            );
        }

        if ($contract && !is_a($class, $contract, true)) {
            throw new \InvalidArgumentException(
                sprintf('Callable %s must be an instance of %s', $class, $contract)
            );
        }

        return [new $class($injectThis), $method];
    }

    /**
     * Extract class and method from toResolve.
     *
     * @param string $callable
     *
     * @return array
     */
    protected static function parseClassName($callable)
    {
        if (preg_match(self::CALLABLE_PATTERN, $callable, $matches)) {
            return [$matches[1], $matches[2]];
        }

        return [$callable, '__invoke'];
    }

    /**
     * @param callable $callable
     *
     * @throws \RuntimeException
     */
    protected static function assertCallable($callable)
    {
        if (!is_callable($callable)) {
            throw new \RuntimeException(
                sprintf(
                    '%s is not resolvable',
                    is_array($callable) || is_object($callable)
                        ? json_encode($callable)
                        : $callable
                )
            );
        }
    }
}
