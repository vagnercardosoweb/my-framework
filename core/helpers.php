<?php

use Core\App;
use Core\Helpers\Arr;
use Core\Helpers\Helper;
use Core\Router;
use Dotenv\Environment\Adapter\EnvConstAdapter;
use Dotenv\Environment\Adapter\PutenvAdapter;
use Dotenv\Environment\Adapter\ServerConstAdapter;
use Dotenv\Environment\DotenvFactory;
use Slim\Http\StatusCode;

if (!function_exists('env')) {
    /**
     * @param string $key
     * @param mixed $default
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
     * @param bool $baseUrl
     * @param bool $version
     *
     * @return bool|string
     */
    function asset(string $file, bool $baseUrl = false, bool $version = false)
    {
        $file = ((!empty($file[0]) && $file[0] !== '/') ? "/{$file}" : $file);
        $path = PUBLIC_FOLDER."{$file}";
        $baseUrl = ($baseUrl ? BASE_URL : '');

        if (file_exists($path)) {
            $version = ($version ? '?v='.substr(md5_file($path), 0, 15) : '');

            return "{$baseUrl}{$file}{$version}";
        }

        return false;
    }
}

if (!function_exists('asset_content')) {
    /**
     * @param string|array $files
     *
     * @return bool|string
     */
    function asset_content($files)
    {
        $contents = [];

        if (!is_array($files)) {
            $files = [$files];
        }

        foreach ($files as $file) {
            $file = ((!empty($file[0]) && $file[0] !== '/') ? "/{$file}" : $file);
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

if (!function_exists('config')) {
    /**
     * @param string $name
     * @param mixed $default
     *
     * @return mixed
     */
    function config(string $name = null, $default = null)
    {
        static $config = [];

        if (empty($config)) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(
                    APP_FOLDER.'/config', FilesystemIterator::SKIP_DOTS
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

if (!function_exists('json')) {
    /**
     * @param mixed $data
     * @param int $status
     * @param int $options
     *
     * @return \Slim\Http\Response
     */
    function json($data, int $status = StatusCode::HTTP_OK, int $options = 0)
    {
        return App::getInstance()->resolve('response')->withJson($data, $status, $options);
    }
}

if (!function_exists('redirect')) {
    /**
     * @param string $name
     * @param array $data
     * @param array $queryParams
     *
     * @return \Slim\Http\Response
     */
    function redirect(string $name, array $data = [], array $queryParams = [])
    {
        try {
            $status = StatusCode::HTTP_FOUND;
            $location = Router::pathFor($name, $data, $queryParams);
        } catch (Exception $e) {
            $status = StatusCode::HTTP_MOVED_PERMANENTLY;
            $queryParams = Helper::httpBuildQuery(array_merge_recursive($data, $queryParams));
            $location = "{$name}{$queryParams}";
        }

        if (App::getInstance()->resolve('request')->isXhr()) {
            return json(['location' => $location], $status);
        }

        return App::getInstance()
            ->resolve('response')
            ->withRedirect($location, $status);
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

        foreach ((array) $values as $key => $value) {
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
     * @param array $data
     *
     * @return bool
     */
    function empty_recursive(array $data)
    {
        if (empty($data)) {
            return true;
        }

        foreach ((array) $data as $key => $value) {
            if (is_array($value)) {
                return empty_recursive($value);
            } else {
                if (empty($value) && $value != '0') {
                    return true;
                }
            }
        }

        return false;
    }
}

if (!function_exists('filter_values')) {
    /**
     * @param mixed $values
     *
     * @return array
     */
    function filter_values($values)
    {
        $result = [];

        if (!is_array($values)) {
            $values = [$values];
        }

        foreach ($values as $key => $value) {
            if (is_array($value)) {
                $result[$key] = filter_values($value);
            } else {
                if (is_int($value)) {
                    $filter = FILTER_SANITIZE_NUMBER_INT;
                } else if (is_float($value)) {
                    $filter = FILTER_SANITIZE_NUMBER_FLOAT;
                } else if (is_string($value)) {
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

if (!function_exists('request_params')) {
    /**
     * @param string $key
     *
     * @return mixed
     */
    function request_params(?string $key = null)
    {
        $params = App::getInstance()->resolve('request')->getParams();
        $params = filter_values($params);

        if (empty($key)) {
            return $params;
        }

        return array_key_exists($key, $params)
            ? $params[$key]
            : null;
    }
}
