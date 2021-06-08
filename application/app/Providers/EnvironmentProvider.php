<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 08/06/2021 Vagner Cardoso
 */

namespace App\Providers;

use Core\App;
use Slim\Http\Environment;

/**
 * Class EnvironmentProvider.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class EnvironmentProvider extends Provider
{
    /**
     * @return string
     */
    public function name(): string
    {
        return 'environment';
    }

    /**
     * @return \Closure
     */
    public function register(): \Closure
    {
        return function () {
            if (!App::isCli()) {
                return new Environment($_SERVER);
            }

            $uri = '/';
            $method = 'get';
            $queryString = '';

            if (isset($_SERVER['argv'][1])) {
                $parse = parse_url($_SERVER['argv'][1]);
                $uri = $parse['path'] ?? '/';
                $queryString = $parse['query'] ?? '';
            }

            if (isset($_SERVER['argv'][2])) {
                $method = $_SERVER['argv'][2];
            }

            return Environment::mock(array_merge($_SERVER, [
                'REQUEST_URI' => rtrim("/console{$uri}", '\/'),
                'QUERY_STRING' => $queryString,
                'REQUEST_METHOD' => strtoupper($method),
            ]));
        };
    }
}
