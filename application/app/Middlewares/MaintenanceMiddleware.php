<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 05/07/2020 Vagner Cardoso
 */

namespace App\Middlewares;

use Core\Env;
use Core\Exception\ResponseException;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\StatusCode;

/**
 * Class MaintenanceMiddleware.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class MaintenanceMiddleware extends Middleware
{
    /**
     * @param \Slim\Http\Request  $request  PSR7 request
     * @param \Slim\Http\Response $response PSR7 response
     * @param callable            $next     Next middleware
     *
     * @throws \Core\Exception\ResponseException
     *
     * @return \Slim\Http\Response
     */
    public function __invoke(Request $request, Response $response, callable $next): Response
    {
        if (true === Env::get('APP_MAINTENANCE', false)) {
            throw new ResponseException('Error 503 (Service Unavailable)', StatusCode::HTTP_SERVICE_UNAVAILABLE);
        }

        return $next($request, $response);
    }
}
