<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 13/02/2020 Vagner Cardoso
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
        // Environment
        Environment::load();

        // PHP Configuration
        $this->registerPhpConfiguration();

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
     * @return bool
     */
    public static function onlyApi(): bool
    {
        return 'true' == env('APP_ONLY_API', false);
    }

    /**
     * @return bool
     */
    public static function isCli(): bool
    {
        return in_array(PHP_SAPI, ['cli', 'phpdbg']);
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
                        throw new \BadMethodCallException(sprintf('Call to undefined method %s::%s()', get_class($controller), $method), E_ERROR);
                    }
                }

                return call_user_func_array([$controller, $method], $params);
            });
        }

        if (!empty($name)) {
            $name = mb_strtolower($name);
            $route->setName($name);
        }

        if (!empty($middleware)) {
            $middlewareManual = config('app.middleware.manual', []);

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
                return call_user_func_array($container->get($name), $params);
            }

            return $container->get($name);
        }

        return false;
    }

    /**
     * @param string|null $folder
     *
     * @return $this
     */
    public function registerFolderRoutes(?string $folder = null): self
    {
        $file = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $folder ?: APP_FOLDER.'/routes',
                \FilesystemIterator::SKIP_DOTS
            )
        );

        $file->rewind();

        while ($file->valid()) {
            if ($file->isFile()) {
                call_user_func(function ($file, $app) {
                    require_once "{$file->getRealPath()}";
                }, $file, $this);
            }

            $file->next();
        }

        return $this;
    }

    /**
     * @param array $middleware
     *
     * @return $this
     */
    public function registerMiddleware(array $middleware = []): self
    {
        if (!$middleware) {
            $middleware = config('app.middleware.automatic', []);
        }

        foreach ($middleware as $name => $middle) {
            if (class_exists($middle)) {
                $this->add($middle);
            }
        }

        return $this;
    }

    /**
     * @param array $providers
     *
     * @return $this
     */
    public function registerProviders(array $providers = []): self
    {
        if (!$providers) {
            $providers = config('app.providers', []);
        }

        foreach ($providers as $provider) {
            if (class_exists($provider)) {
                $provider = new $provider($this);

                foreach (['register', 'boot'] as $method) {
                    if (method_exists($provider, $method)) {
                        call_user_func([$provider, $method]);
                    }
                }
            }
        }

        return $this;
    }

    /**
     * @return void
     */
    private function registerPhpConfiguration(): void
    {
        $charset = env('APP_CHARSET', 'UTF-8');
        $locale = env('APP_LOCALE', 'pt_BR');

        ini_set('default_charset', $charset);
        date_default_timezone_set(env('APP_TIMEZONE', 'America/Sao_Paulo'));
        mb_internal_encoding($charset);
        setlocale(LC_ALL, $locale, "{$locale}.{$charset}");

        // Configurations de err

        ini_set('log_errors', true);
        ini_set('error_log', sprintf(env('INI_ERROR_LOG', APP_FOLDER.'/storage/logs/php-%s.log'), date('dmY')));
        ini_set('display_errors', env('INI_DISPLAY_ERRORS', ini_get('display_errors')));
        ini_set('display_startup_errors', env('INI_DISPLAY_STARTUP_ERRORS', ini_get('display_startup_errors')));

        if ('development' == env('APP_ENV', 'development')) {
            error_reporting(E_ALL);
        } else {
            error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED);
        }

        set_error_handler(function ($level, $message, $file = '', $line = 0) {
            if (error_reporting() & $level) {
                throw new \ErrorException($message, 0, $level, $file, $line);
            }
        });
    }
}
