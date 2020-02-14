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
use Slim\Http\Response;
use Slim\Http\StatusCode;

/**
 * Class Router.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class Router
{
    /**
     * @param string $name
     *
     * @return bool
     */
    public static function isCurrent(string $name): bool
    {
        $router = str_replace(BASE_URL, '', self::pathFor($name));
        $current = App::getInstance()
            ->resolve('request')
            ->getUri()
            ->getPath()
        ;

        if ('/' !== substr($current, 0, 1)) {
            $current = "/{$current}";
        }

        if ($router === $current) {
            return true;
        }

        return false;
    }

    /**
     * @param string $name
     * @param array  $data
     * @param array  $queryParams
     * @param string $hash
     *
     * @return string
     */
    public static function pathFor(string $name, array $data = [], array $queryParams = [], string $hash = ''): string
    {
        $name = strtolower($name);
        $baseUrl = '';

        if (':' === $name[0]) {
            $name = substr($name, 1);
            $baseUrl = BASE_URL;
        }

        return $baseUrl.App::getInstance()
            ->resolve('router')
            ->pathFor($name, $data, $queryParams)
            .$hash;
    }

    /**
     * @param string|array $routes
     *
     * @return bool
     */
    public static function hasCurrent($routes): bool
    {
        if (empty($routes)) {
            return false;
        }

        $current = App::getInstance()
            ->resolve('request')
            ->getUri()
            ->getPath()
        ;

        foreach ((array)$routes as $route) {
            if (false !== mb_strpos($current, $route)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $name
     * @param array  $data
     * @param array  $queryParams
     * @param int    $status
     * @param string $hash
     *
     * @return \Slim\Http\Response
     */
    public static function redirect(string $name, array $data = [], array $queryParams = [], int $status = StatusCode::HTTP_FOUND, string $hash = ''): Response
    {
        try {
            $location = self::pathFor($name, $data, $queryParams, $hash);
        } catch (\Exception $e) {
            $queryParams = Helper::httpBuildQuery(array_merge_recursive($data, $queryParams));
            $location = "{$name}{$queryParams}{$hash}";
        }

        if (App::getInstance()->resolve('request')->isXhr()) {
            return json(['location' => $location], $status);
        }

        return App::getInstance()
            ->resolve('response')
            ->withRedirect(
                $location, $status
            )
        ;
    }
}
