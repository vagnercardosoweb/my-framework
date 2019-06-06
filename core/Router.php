<?php

/**
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>.
 *
 * @author    Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @copyright 02/06/19 Vagner Cardoso
 */

namespace Core {
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
         * @param string|array $router
         *
         * @return bool
         */
        public static function hasCurrent($router): bool
        {
            if (empty($router)) {
                return false;
            }

            $current = App::getInstance()
                ->resolve('request')
                ->getUri()
                ->getPath()
            ;

            foreach ((array) $router as $route) {
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
         * @param string $hash
         *
         * @return string
         */
        public static function pathFor(string $name, array $data = [], array $queryParams = [], string $hash = null): string
        {
            $name = strtolower($name);
            $baseUrl = '';

            if (':' === $name[0]) {
                $name = substr($name, 1);
                $baseUrl = BASE_URL;
            }

            if (!empty($hash)) {
                $hash = "#{$hash}";
            }

            return $baseUrl.App::getInstance()
                ->resolve('router')
                ->pathFor(
                    $name, $data, $queryParams
                ).$hash;
        }
    }
}
