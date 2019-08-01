<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 01/08/2019 Vagner Cardoso
 */

use Core\App;
use Core\Helpers\Arr;
use Core\Helpers\Helper;
use Core\Helpers\Validate;
use Core\Router;
use Dotenv\Environment\Adapter\EnvConstAdapter;
use Dotenv\Environment\Adapter\PutenvAdapter;
use Dotenv\Environment\Adapter\ServerConstAdapter;
use Dotenv\Environment\DotenvFactory;
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
        static $variables;

        if (empty($variables)) {
            $variables = (new DotenvFactory([
                new EnvConstAdapter(),
                new PutenvAdapter(),
                new ServerConstAdapter(),
            ]))->createImmutable();
        }

        if (!$value = $variables->get($key)) {
            return $default;
        }

        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
                break;
            case 'false':
            case '(false)':
                return false;
                break;
            case 'empty':
            case '(empty)':
                return '';
                break;
            case 'null':
            case '(null)':
                return null;
                break;
        }

        return trim($value);
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
        if (!empty($value)) {
            return preg_replace(
                '/[^0-9]/', '', $value
            );
        }
    }
}

if (!function_exists('config')) {
    /**
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     */
    function config(string $name = null, $default = null)
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

        return Arr::get(
            $config, $name, $default
        );
    }
}

if (!function_exists('logger')) {
    /**
     * @param string $message
     * @param array  $context
     * @param string $type
     * @param string $file
     *
     * @return bool|\Monolog\Logger
     */
    function logger($message, array $context = [], $type = 'info', $file = null)
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
     * @param array  $context
     * @param int    $status
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
    /**
     * @param string $template
     * @param array  $context
     *
     * @return mixed
     */
    function view_fetch($template, array $context = [])
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
    function json($data, int $status = StatusCode::HTTP_OK, int $options = 0)
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
    /**
     * @param string $value
     *
     * @return string
     */
    function __($value)
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
    function htmlentities_recursive($values)
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
    function empty_recursive($data)
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
    function params(?string $name = null)
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
    function filter_params($values)
    {
        $result = [];

        if (!is_array($values)) {
            $values = [$values];
        }

        foreach ($values as $key => $value) {
            if (is_array($value)) {
                $result[$key] = filter_params($value);
            } else {
                if (is_int($value)) {
                    $filter = FILTER_SANITIZE_NUMBER_INT;
                } elseif (is_float($value)) {
                    $filter = FILTER_SANITIZE_NUMBER_FLOAT;
                } elseif (is_string($value)) {
                    $filter = FILTER_SANITIZE_STRING;
                } else {
                    $filter = FILTER_DEFAULT;
                }

                $result[$key] = addslashes(strip_tags(
                    trim(filter_var($value, $filter))
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
    function path_for(string $name, array $data = [], array $queryParams = [], ?string $hash = null): string
    {
        return Router::pathFor($name, $data, $queryParams, $hash);
    }
}

if (!function_exists('header_location')) {
    /**
     * @param string $route
     * @param int    $status
     * @param bool   $replace
     */
    function header_location(string $route, bool $replace = true, int $status = StatusCode::HTTP_MOVED_PERMANENTLY)
    {
        header("Location: {$route}", $replace, $status);

        exit;
    }
}

if (!function_exists('redirect')) {
    /**
     * @param string      $name
     * @param array       $data
     * @param array       $queryParams
     * @param string|null $hash
     * @param int         $status
     *
     * @return \Slim\Http\Response
     */
    function redirect(string $name, array $data = [], array $queryParams = [], int $status = StatusCode::HTTP_FOUND, ?string $hash = null)
    {
        return Router::redirect($name, $data, $queryParams, $status, $hash);
    }
}

if (!function_exists('is_route')) {
    /**
     * @param string $name
     *
     * @return bool|string
     */
    function is_route(string $name)
    {
        Router::isCurrent($name);
    }
}

if (!function_exists('has_route')) {
    /**
     * @param mixed $routes
     *
     * @return bool
     */
    function has_route($routes)
    {
        return Router::hasCurrent($routes);
    }
}

if (!function_exists('is_php_cli')) {
    /**
     * @return bool
     */
    function is_php_cli()
    {
        return Helper::isPhpCli();
    }
}
