<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 05/07/2020 Vagner Cardoso
 */

namespace Core;

use Core\Helpers\CallableResolver;
use Core\Helpers\Helper;
use Core\Helpers\Path;
use Core\Interfaces\EventListener;
use Core\Interfaces\Middleware;
use Core\Interfaces\ServiceProvider;
use Slim\App as SlimApp;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class App.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class App extends SlimApp
{
    /**
     * @var \Core\App
     */
    protected static $instance;

    /**
     * @var array
     */
    protected $groupNamespaces = [];

    /**
     * @var string
     */
    protected $defaultNamespace = 'App/Controllers';

    /**
     * App constructor.
     */
    public function __construct()
    {
        // Environment
        Env::load();

        // PHP Configuration
        $this->registerPhpConfiguration();

        // Slim settings
        parent::__construct([
            'settings' => array_merge([
                'httpVersion' => '1.1',
                'responseChunkSize' => 4096,
                'outputBuffering' => 'append',
                'determineRouteBeforeAppMiddleware' => true,
                'displayErrorDetails' => ('development' === Env::get('APP_ENV', 'development')),
                'addContentLengthHeader' => true,
                'routerCacheFile' => false,
            ], Config::get('app.slim', [])),
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
        return true === Env::get('APP_ONLY_API', false);
    }

    /**
     * @param string|array               $methods
     * @param string                     $pattern
     * @param string|\Closure            $callable
     * @param strin|null                 $name
     * @param string|array|\Closure|null $middleware
     *
     * @return \Slim\Interfaces\RouteInterface
     */
    public function route($methods, $pattern, $callable, $name = null, $middleware = null)
    {
        $methods = '*' == $methods ? ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'] : $methods;
        $methods = (is_string($methods) ? explode(',', mb_strtoupper($methods)) : $methods);
        $pattern = (string)$pattern;

        $route = $this->map($methods, $pattern, $this->handleCallableRouter($callable));

        if (!empty($name)) {
            $name = mb_strtolower($name);
            $route->setName($name);
        }

        if (!empty($middleware)) {
            $this->addMiddlewareInRouteOrGroup($route, $middleware);
        }

        return $route;
    }

    /**
     * @return bool
     */
    public static function isCli(): bool
    {
        return in_array(PHP_SAPI, ['cli', 'phpdbg']);
    }

    /**
     * @param string|array      $pattern
     * @param callable|\Closure $callable
     *
     * @return \Slim\Interfaces\RouteGroupInterface
     */
    public function group($pattern, $callable)
    {
        $currentNamespaces = $this->groupNamespaces;
        $groupMiddleware = [];

        if (is_array($pattern)) {
            if (!empty($pattern['middleware'])) {
                $groupMiddleware = $pattern['middleware'];
            }

            if (!empty($pattern['namespace'])) {
                array_push(
                    $this->groupNamespaces,
                    ucwords($pattern['namespace'])
                );
            }

            $pattern = $pattern['path'] ?? $pattern['prefix'] ?? '';
        }

        $group = parent::group($pattern, $callable);

        if (!empty($groupMiddleware)) {
            $this->addMiddlewareInRouteOrGroup($group, $groupMiddleware);
        }

        $this->groupNamespaces = $currentNamespaces;

        return $group;
    }

    /**
     * @return \Core\App
     */
    public function registerRoutesFolder()
    {
        $routes = [];

        foreach (glob_recursive(Path::app('/routes/**')) as $route) {
            if (is_file($route) && !is_dir($route)) {
                $routes[] = $route;
            }
        }

        $this->registerRoutes($routes);

        return $this;
    }

    /**
     * @param array $routes
     *
     * @return \Core\App
     */
    public function registerRoutes(array $routes = []): App
    {
        if (!$routes) {
            $routes = Config::get('app.routes.app', []);
        }

        if (Env::get('ENABLE_OPTIONS_ALL_ROUTES', false)) {
            $this->options('/{routes:.*}', function ($request, $response) {
                return $response->withStatus(200);
            });
        }

        foreach ($routes as $path) {
            call_user_func(function ($path, $app) {
                require_once "{$path}";
            }, $path, $this);
        }

        return $this;
    }

    /**
     * @param array $providers
     *
     * @return \Core\App
     */
    public function registerProviders(array $providers = []): App
    {
        if (!$providers) {
            $providers = Config::get('app.providers', []);
        }

        foreach ($providers as $class) {
            if (!is_a($class, ServiceProvider::class, true)) {
                throw new \InvalidArgumentException(
                    sprintf('Provider %s must be an instance of %s', $class, ServiceProvider::class)
                );
            }

            $container = $this->getContainer();
            $instance = new $class($container);

            foreach ((array)$instance->name() as $name) {
                $container[$name] = $instance->register();
            }

            if (method_exists($instance, 'boot')) {
                call_user_func([$instance, 'boot']);
            }
        }

        return $this;
    }

    /**
     * @param array $middleware
     *
     * @return \Core\App
     */
    public function registerMiddleware(array $middleware = []): App
    {
        if (!$middleware) {
            $middleware = Config::get('app.middleware.app', []);
        }

        foreach ($middleware as $name => $class) {
            if (!is_a($class, Middleware::class, true)) {
                throw new \InvalidArgumentException(
                    sprintf('Middleware %s must be an instance of %s', $class, Middleware::class)
                );
            }

            $this->add($class);
        }

        return $this;
    }

    /**
     * @param array $events
     *
     * @return \Core\App
     */
    public function registerEvents(array $events = []): App
    {
        if (!$events) {
            $events = Config::get('app.events', []);
        }

        foreach ($events as $class) {
            if (!$event = $this->resolve('event')) {
                break;
            }

            $callable = CallableResolver::resolve(
                sprintf('%s:register', $class),
                $this->getContainer(),
                EventListener::class
            );

            $event->on($callable[0]->name(), $callable);
        }

        return $this;
    }

    /**
     * @param string $name
     * @param ...... $params
     *
     * @return mixed
     */
    public function resolve(string $name)
    {
        $container = $this->getContainer();

        if ($container->has($name)) {
            if (!is_callable($container->get($name))) {
                return $container->get($name);
            }

            $params = func_get_args();
            array_shift($params);

            return call_user_func_array(
                $container->get($name), $params
            );
        }

        return null;
    }

    /**
     * @param string $namespace
     *
     * @return \Core\App
     */
    public function setDefaultNamespace(string $namespace): App
    {
        $this->defaultNamespace = $namespace;

        return $this;
    }

    /**
     * @throws \ErrorException
     *
     * @return void
     */
    private function registerPhpConfiguration(): void
    {
        $locale = Env::get('APP_LOCALE', 'pt_BR');
        $charset = Env::get('APP_CHARSET', 'UTF-8');

        ini_set('default_charset', $charset);
        date_default_timezone_set(Env::get('APP_TIMEZONE', 'America/Sao_Paulo'));
        mb_internal_encoding($charset);
        setlocale(LC_ALL, $locale, "{$locale}.{$charset}");

        ini_set('display_errors', Env::get('PHP_DISPLAY_ERRORS', ini_get('display_errors')));
        ini_set('display_startup_errors', Env::get('PHP_DISPLAY_STARTUP_ERRORS', ini_get('display_startup_errors')));

        ini_set('log_errors', Env::get('PHP_LOG_ERRORS', 'true'));
        ini_set('error_log', sprintf(Env::get('PHP_ERROR_LOG', Path::storage('/logs/php/%s.log')), date('dmY')));

        if ('development' === Env::get('APP_ENV', 'development')) {
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

    /**
     * @param string|callable $callable
     *
     * @return \Closure
     */
    private function handleCallableRouter($callable): \Closure
    {
        if ($callable instanceof \Closure) {
            return $callable;
        }

        $namespace = $this->defaultNamespace;
        $groupNamespaces = $this->groupNamespaces;

        foreach ($groupNamespaces as $n) {
            while ('/' === $n[strlen($n) - 1]) {
                $n = substr($n, 0, -1);
            }

            $namespace .= "/{$n}";
        }

        return function (Request $request, Response $response, array $params) use ($callable, $namespace) {
            try {
                list($name, $originalMethod) = (explode('@', $callable) + [1 => null]);

                $method = mb_strtolower($request->getMethod()).ucfirst($originalMethod);
                $namespace = sprintf('%s/%s', $namespace, $name);
                $namespace = str_ireplace('/', '\\', $namespace);

                $controller = new $namespace($request, $response, $this);

                if (!Helper::objectMethodExists($controller, [$method, '__call', '__callStatic'])) {
                    $method = $originalMethod ?? 'index';

                    if (!method_exists($controller, $method)) {
                        throw new \BadMethodCallException(
                            sprintf('Call to undefined method %s::%s()', get_class($controller), $method)
                        );
                    }
                }

                if (App::isCli()) {
                    $params = array_merge($params, $request->getQueryParams());
                }

                $result = call_user_func_array([$controller, $method], $params);

                if (is_array($result) || $json = Helper::decodeJson($result, true)) {
                    return $response->withJson($json ?? $result);
                }

                return $result;
            } catch (\Exception $e) {
                return call_user_func_array(
                    $this->get('errorHandler'),
                    [$request, $response, $e]
                );
            }
        };
    }

    /**
     * @param \Slim\Interfaces\RouteInterface|\Slim\Interfaces\RouteGroupInterface $route
     * @param string|array                                                         $middleware
     */
    private function addMiddlewareInRouteOrGroup($route, $middleware): void
    {
        $manual = Config::get('app.middleware.manual', []);

        if (!is_array($middleware)) {
            $middleware = [$middleware];
        }

        sort($middleware);

        foreach ($middleware as $middle) {
            if (array_key_exists($middle, $manual)) {
                $middle = $manual[$middle];
            }

            if (class_exists($middle) || $middle instanceof \Closure) {
                $route->add($middle);
            }
        }
    }
}
