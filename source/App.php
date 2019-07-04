<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 04/07/2019 Vagner Cardoso
 */

namespace Core;

use Core\Helpers\Helper;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class App.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class App extends \Slim\App
{
    /**
     * @var \Core\App
     */
    private static $instance;

    /**
     * App constructor.
     */
    public function __construct()
    {
        // Dotenv
        Loader::dotEnvironment();

        // Slim settings
        parent::__construct([
            'settings' => array_merge([
                'httpVersion' => '1.1',
                'responseChunkSize' => 4096,
                'outputBuffering' => 'append',
                'determineRouteBeforeAppMiddleware' => true,
                'displayErrorDetails' => ('development' == env('APP_ENV', 'development')),
                'addContentLengthHeader' => true,
                'routerCacheFile' => false,
            ], config('app.slim', [])),
        ]);

        // Default settings php
        Loader::defaultPhpConfig();
    }

    /**
     * @return \Core\App
     */
    public static function getInstance()
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @param string|array          $methods
     * @param string                $pattern
     * @param string|\Closure       $callable
     * @param string                $name
     * @param string|array|\Closure $middleware
     *
     * @return \Slim\Interfaces\RouteInterface
     */
    public function route($methods, $pattern, $callable, $name = null, $middleware = null)
    {
        $methods = '*' == $methods ? ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'] : $methods;
        $methods = (is_string($methods) ? explode(',', mb_strtoupper($methods)) : $methods);
        $pattern = (string)$pattern;

        if ($callable instanceof \Closure) {
            $route = $this->map($methods, $pattern, $callable);
        } else {
            $route = $this->map($methods, $pattern, function (Request $request, Response $response, array $params) use ($callable) {
                list($namespace, $originalMethod) = (explode('@', $callable) + [1 => null]);
                $method = mb_strtolower($request->getMethod()).ucfirst($originalMethod);

                if (!strripos($namespace, 'Controller')) {
                    $namespace = "{$namespace}Controller";
                }

                /** @var \Slim\Route $route */
                if ($route = $request->getAttribute('route')) {
                    foreach (array_reverse($route->getGroups()) as $group) {
                        if (property_exists($group, 'namespaces')) {
                            foreach ($group->namespaces as $n) {
                                $n = (('/' !== $n[strlen($n) - 1]) ? "{$n}/" : $n);
                                $namespace = "{$n}{$namespace}";
                            }
                        }
                    }
                }

                $namespace = 'App\\Controllers\\'.str_ireplace('/', '\\', $namespace);
                $controller = new $namespace($request, $response, $this);

                if (!Helper::objectMethodExists($controller, [$method, '__call', '__callStatic'])) {
                    $method = ($originalMethod ?: 'index');

                    if (!method_exists($controller, $method)) {
                        throw new \BadMethodCallException(
                            sprintf('Call to undefined method %s::%s()', get_class($controller), $method), E_ERROR
                        );
                    }
                }

                return call_user_func_array(
                    [$controller, $method], $params
                );
            });
        }

        if (!empty($name)) {
            $name = mb_strtolower($name);
            $route->setName($name);
        }

        if (!empty($middleware)) {
            $middlewareManual = config('app.middlewares.manual', []);

            if (!is_array($middleware)) {
                $middleware = [$middleware];
            }

            sort($middleware);

            foreach ($middleware as $middle) {
                if ($middle instanceof \Closure) {
                    $route->add($middle);
                } else {
                    if (array_key_exists($middle, $middlewareManual)) {
                        $route->add($middlewareManual[$middle]);
                    }
                }
            }
        }

        return $route;
    }

    /**
     * @param string|array      $pattern
     * @param callable|\Closure $callable
     *
     * @return \Slim\Interfaces\RouteGroupInterface
     */
    public function group($pattern, $callable)
    {
        $namespace = null;

        if (!empty($pattern) && is_array($pattern)) {
            if (!empty($pattern['namespace'])) {
                $namespace = $pattern['namespace'];
            }

            $pattern = (!empty($pattern['prefix'])
                ? $pattern['prefix']
                : '');
        }

        $group = parent::group($pattern, $callable);

        if (!empty($namespace)) {
            $group->namespaces[] = $namespace;
        }

        return $group;
    }

    /**
     * @param string $name
     * @param mixed  $params
     *
     * @return mixed
     */
    public function resolve(string $name, ...$params)
    {
        $container = $this->getContainer();

        if ($container->has($name)) {
            if (is_callable($container->get($name))) {
                return call_user_func_array(
                    $container->get($name), $params
                );
            }

            return $container->get($name);
        }

        return false;
    }
}
