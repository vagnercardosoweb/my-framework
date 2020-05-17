<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 01/03/2020 Vagner Cardoso
 */

use Core\App;
use Core\Config;
use Core\Env;
use Core\Helpers\CallableResolver;
use Core\Helpers\Helper;
use Core\Logger;
use Core\Router;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\StatusCode;

// CONSTANTS

if (!defined('E_USER_SUCCESS')) {
    define('E_USER_SUCCESS', 'success');
}

if (!defined('DATE_BR')) {
    define('DATE_BR', 'd/m/Y');
}

if (!defined('DATE_TIME_BR')) {
    define('DATE_TIME_BR', 'd/m/Y H:i:s');
}

if (!defined('DATE_DATABASE')) {
    define('DATE_DATABASE', 'Y-m-d H:i:s');
}

if (!function_exists('env')) {
    /**
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    function env(string $key, $default = null)
    {
        return Env::get($key, $default);
    }
}

if (!function_exists('config')) {
    /**
     * @param string|null $key
     * @param null        $default
     *
     * @return mixed
     */
    function config(?string $key = null, $default = null)
    {
        if (empty($key)) {
            return Config::all();
        }

        return Config::get($key, $default);
    }
}

if (!function_exists('asset')) {
    /**
     * @param string      $file
     * @param string|bool $baseUrl
     * @param bool        $version
     *
     * @return string|null
     */
    function asset(string $file, $baseUrl = '', bool $version = false): ?string
    {
        if (!is_string($baseUrl)) {
            $baseUrl = defined('BASE_URL') ? constant('BASE_URL') : '';
        }

        return \Core\Helpers\Asset::path($file, $baseUrl, $version);
    }
}

if (!function_exists('asset_source')) {
    /**
     * @param string|array $files
     *
     * @return string|null
     */
    function asset_source($files): ?string
    {
        return \Core\Helpers\Asset::source($files);
    }
}

if (!function_exists('glob_recursive')) {
    /**
     * @param string $pattern
     * @param int    $flags
     *
     * @return array
     */
    function glob_recursive($pattern, $flags = 0)
    {
        $files = glob($pattern, $flags);

        foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR | GLOB_NOSORT) as $dir) {
            $files = array_merge($files, glob_recursive($dir.'/'.basename($pattern), $flags));
        }

        return $files;
    }
}

if (!function_exists('onlyNumber')) {
    /**
     * @param string $value
     *
     * @return int|string
     */
    function onlyNumber($value)
    {
        return Helper::onlyNumber($value);
    }
}

if (!function_exists('app')) {
    /**
     * @return \Core\App
     */
    function app(): App
    {
        return App::getInstance();
    }
}

if (!function_exists('response')) {
    /**
     * @return \Slim\Http\Response|null
     */
    function response(): ?Response
    {
        return app()->resolve('response');
    }
}

if (!function_exists('request')) {
    /**
     * @return \Slim\Http\Request|null
     */
    function request(): ?Request
    {
        return app()->resolve('request');
    }
}

if (!function_exists('logger')) {
    /**
     * @return Logger
     */
    function logger()
    {
        return \app()->resolve('logger');
    }
}

if (!function_exists('view')) {
    /**
     * @return \Core\View
     */
    function view(): Core\View
    {
        return app()->resolve('view');
    }
}

if (!function_exists('json')) {
    /**
     * @param mixed $data
     * @param int   $status
     *
     * @return \Slim\Http\Response
     */
    function json($data, int $status = StatusCode::HTTP_OK): Response
    {
        if (response()) {
            return response()->withJson($data, $status);
        }

        http_response_code($status);
        header('Content-type: application/json');

        $json = json_encode($data);

        if (0 !== ob_get_level()) {
            ob_clean();
        }

        echo $json;
        exit;
    }
}

if (!function_exists('__')) {
    /**
     * @param string $value
     *
     * @return string
     */
    function __(string $value): string
    {
        return html_entity_decode($value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('htmlentities_recursive')) {
    /**
     * @param mixed $values
     *
     * @return array
     */
    function htmlentities_recursive($values): array
    {
        $data = [];

        foreach ((array)$values as $key => $value) {
            if (is_array($value)) {
                $data[$key] = htmlentities_recursive($value);
            } else {
                if (is_string($value)) {
                    $value = htmlentities($value, ENT_QUOTES, 'UTF-8');
                }

                $data[$key] = $value;
            }
        }

        return $data;
    }
}

if (!function_exists('empty_recursive')) {
    /**
     * @param array|object $data
     *
     * @return bool
     */
    function empty_recursive($data): bool
    {
        return Helper::emptyArrayRecursive($data);
    }
}

if (!function_exists('params')) {
    /**
     * @param string $name
     *
     * @return mixed
     */
    function params(string $name = '')
    {
        $params = request()->getParams();
        $params = filter_params($params);

        if (empty($name)) {
            return $params;
        }

        if (array_key_exists($name, $params)) {
            return $params[$name];
        }

        return null;
    }
}

if (!function_exists('filter_params')) {
    /**
     * @param mixed $values
     *
     * @return array
     */
    function filter_params($values): array
    {
        $result = [];

        if (!is_array($values)) {
            $values = [$values];
        }

        foreach ($values as $key => $value) {
            if (is_array($value)) {
                $result[$key] = filter_params($value);
            } else {
                $result[$key] = addslashes(strip_tags(
                    trim(filter_var($value, FILTER_DEFAULT))
                ));
            }
        }

        return $result;
    }
}

if (!function_exists('path_for')) {
    /**
     * @param string $name
     * @param array  $data
     * @param array  $queryParams
     * @param string $hash
     *
     * @return string
     */
    function path_for(string $name, array $data = [], array $queryParams = [], string $hash = ''): string
    {
        return Router::pathFor($name, $data, $queryParams, $hash);
    }
}

if (!function_exists('header_location')) {
    /**
     * @param string $route
     * @param int    $status
     *
     * @return void
     */
    function header_location(string $route, int $status = StatusCode::HTTP_MOVED_PERMANENTLY): void
    {
        header("Location: {$route}", true, $status);

        exit;
    }
}

if (!function_exists('redirect')) {
    /**
     * @param string $name
     * @param array  $data
     * @param array  $queryParams
     * @param int    $status
     * @param string $hash
     *
     * @return \Slim\Http\Response
     */
    function redirect(string $name, array $data = [], array $queryParams = [], int $status = StatusCode::HTTP_FOUND, string $hash = ''): Response
    {
        return Router::redirect($name, $data, $queryParams, $status, $hash);
    }
}

if (!function_exists('is_route')) {
    /**
     * @param string $name
     *
     * @return bool
     */
    function is_route(string $name): bool
    {
        return Router::isCurrent($name);
    }
}

if (!function_exists('has_route')) {
    /**
     * @param mixed $routes
     *
     * @return bool
     */
    function has_route($routes): bool
    {
        return Router::hasCurrent($routes);
    }
}

if (!function_exists('retry')) {
    /**
     * Retry an operation a given number of times.
     *
     * @param int             $times
     * @param callable|string $callback
     * @param int             $sleep
     * @param callable        $when
     *
     * @throws \Exception
     *
     * @return mixed
     */
    function retry($times, $callback, $sleep = 0, $when = null)
    {
        $attempts = 0;

        beginning:
        $attempts++;
        $times--;

        try {
            return call_user_func(CallableResolver::resolve($callback), $attempts);
        } catch (\Exception $e) {
            if ($times < 1 || ($when && !$when($e))) {
                throw $e;
            }

            if ($sleep) {
                usleep($sleep * 1000);
            }

            goto beginning;
        }
    }
}

if (!function_exists('class_basename')) {
    /**
     * @param string|object $class
     *
     * @return string
     */
    function class_basename($class)
    {
        $class = is_object($class) ? get_class($class) : $class;

        return basename(str_replace('\\', '/', $class));
    }
}
