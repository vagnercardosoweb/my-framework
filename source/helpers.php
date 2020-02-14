<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 13/02/2020 Vagner Cardoso
 */

use Core\App;
use Core\Environment;
use Core\Helpers\Arr;
use Core\Helpers\Helper;
use Core\Helpers\Validate;
use Core\Logger;
use Core\Router;
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
        return Environment::get($key, $default);
    }
}

if (!function_exists('asset')) {
    /**
     * @param string $file
     * @param bool   $baseUrl
     * @param bool   $version
     *
     * @return bool|string
     */
    function asset(string $file, bool $baseUrl = false, bool $version = false)
    {
        $file = ((!empty($file[0]) && '/' !== $file[0]) ? "/{$file}" : $file);
        $path = PUBLIC_FOLDER."{$file}";
        $baseUrl = ($baseUrl ? BASE_URL : '');

        if (file_exists($path)) {
            $version = ($version ? '?v='.substr(md5_file($path), 0, 15) : '');

            return "{$baseUrl}{$file}{$version}";
        }

        return false;
    }
}

if (!function_exists('asset_source')) {
    /**
     * @param string|array $files
     *
     * @return bool|string
     */
    function asset_source($files)
    {
        $contents = [];

        if (!is_array($files)) {
            $files = [$files];
        }

        foreach ($files as $file) {
            $file = ((!empty($file[0]) && '/' !== $file[0]) ? "/{$file}" : $file);
            $filePath = PUBLIC_FOLDER."{$file}";

            if (file_exists($filePath)) {
                $contents[] = file_get_contents(
                    $filePath
                );
            }
        }

        return implode('', $contents);
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

if (!function_exists('config')) {
    /**
     * @param mixed  $default
     * @param string $name
     *
     * @return mixed
     */
    function config(string $name = '', $default = null)
    {
        static $config = [];

        if (empty($config)) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(
                    APP_FOLDER.'/config',
                    FilesystemIterator::SKIP_DOTS
                )
            );

            $iterator->rewind();

            while ($iterator->valid()) {
                $basename = $iterator->getBasename('.php');
                $content = include "{$iterator->getRealPath()}";
                $config[$basename] = $content;
                $iterator->next();
            }
        }

        return Arr::get($config, $name, $default);
    }
}

if (!function_exists('logger')) {
    /**
     * @param string $message
     * @param array  $context
     * @param string $type
     * @param string $file
     *
     * @return Logger|bool
     */
    function logger(string $message, array $context = [], string $type = 'info', string $file = '')
    {
        return App::getInstance()
            ->resolve('logger')
            ->filename($file)
            ->{$type}(
                $message,
                $context
            );
    }
}

if (!function_exists('view')) {
    /**
     * @param string $template
     * @param int    $status
     * @param array  $context
     *
     * @return mixed
     */
    function view($template, array $context = [], $status = StatusCode::HTTP_OK)
    {
        $response = App::getInstance()->resolve('response');

        return App::getInstance()
            ->resolve('view')
            ->render(
                $response,
                $template,
                $context,
                $status
            )
        ;
    }
}

if (!function_exists('view_fetch')) {
    function view_fetch(string $template, array $context = []): string
    {
        return App::getInstance()
            ->resolve('view')
            ->fetch(
                $template,
                $context
            )
        ;
    }
}

if (!function_exists('json')) {
    /**
     * @param mixed $data
     * @param int   $status
     * @param int   $options
     *
     * @return \Slim\Http\Response
     */
    function json($data, int $status = StatusCode::HTTP_OK, int $options = 0): Response
    {
        return App::getInstance()
            ->resolve('response')
            ->withJson(
                $data, $status, $options
            )
        ;
    }
}

if (!function_exists('__')) {
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
        return Validate::emptyArrayRecursive($data);
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
        $params = App::getInstance()->resolve('request')->getParams();
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
     * @param bool   $replace
     * @param int    $status
     *
     * @return void
     */
    function header_location(string $route, bool $replace = true, int $status = StatusCode::HTTP_MOVED_PERMANENTLY): void
    {
        header("Location: {$route}", $replace, $status);

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
