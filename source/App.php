<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author    Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @copyright 31/05/2019 Vagner Cardoso
 */

namespace Core {
    use BadMethodCallException;
    use Closure;
    use Core\Helpers\Helper;
    use Slim\Http\Request;
    use Slim\Http\Response;

    /**
     * Class App
     *
     * @author  Vagner Cardoso <vagnercardosoweb@gmail.com>
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

            // Configuraçoes do slim
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

            // Configuração da aplicação
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
         * @param string|array|\Closure $middlewares
         *
         * @return \Slim\Interfaces\RouteInterface
         */
        public function route($methods, $pattern, $callable, $name = null, $middlewares = null)
        {
            // Variavéis
            $methods = (is_string($methods) ? explode(',', mb_strtoupper($methods)) : $methods);
            $pattern = (string)$pattern;

            // Verifica se o callable e uma closure
            if ($callable instanceof Closure) {
                $route = $this->map($methods, $pattern, $callable);
            } else {
                $route = $this->map($methods, $pattern, function (Request $request, Response $response, array $params) use ($callable) {
                    // Separa o namespace e método
                    list($namespace, $originalMethod) = (explode('@', $callable) + [1 => null]);
                    $method = mb_strtolower($request->getMethod()).ucfirst($originalMethod);

                    // Valida se existe o `Controller` na string
                    if (!strripos($namespace, 'Controller')) {
                        $namespace = "{$namespace}Controller";
                    }

                    /*
                     * Percorre os grupos procurando
                     * por NAMESPACES para auto completar
                     */

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

                    // Inicia o controller
                    $namespace = 'App\\Controllers\\'.str_ireplace('/', '\\', $namespace);
                    $controller = new $namespace($request, $response, $this);

                    // Verifica se existe o método
                    if (!Helper::objectMethodExists($controller, [$method, '__call', '__callStatic'])) {
                        // Verifica se o método original existe
                        $method = ($originalMethod ?: 'index');

                        if (!method_exists($controller, $method)) {
                            throw new BadMethodCallException(
                                sprintf('Call to undefined method %s::%s()', get_class($controller), $method), E_ERROR
                            );
                        }
                    }

                    return call_user_func_array(
                        [$controller, $method], $params
                    );
                });
            }

            // Adiciona o nome na rota
            if (!empty($name)) {
                $name = mb_strtolower($name);
                $route->setName($name);
            }

            // Adiciona middlewares na rota
            if (!empty($middlewares)) {
                $middlewaresManual = config('app.middlewares.manual', []);

                if (!is_array($middlewares)) {
                    $middlewares = [$middlewares];
                }

                sort($middlewares);

                foreach ($middlewares as $middleware) {
                    if ($middleware instanceof Closure) {
                        $route->add($middleware);
                    } else {
                        if (array_key_exists($middleware, $middlewaresManual)) {
                            $route->add($middlewaresManual[$middleware]);
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
            // Variávies
            $namespace = null;

            // Verifica o pattern e caso
            // seja um array, trata
            if (!empty($pattern) && is_array($pattern)) {
                if (!empty($pattern['namespace'])) {
                    $namespace = $pattern['namespace'];
                }

                $pattern = (!empty($pattern['prefix'])
                    ? $pattern['prefix']
                    : '');
            }

            // Executa o group
            $group = parent::group($pattern, $callable);

            // namespaces
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
}
