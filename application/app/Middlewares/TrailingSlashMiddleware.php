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

use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class TrailingSlashMiddleware.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class TrailingSlashMiddleware extends Middleware
{
    /**
     * @param \Slim\Http\Request  $request  PSR7 request
     * @param \Slim\Http\Response $response PSR7 response
     * @param callable            $next     Next middleware
     *
     * @return \Slim\Http\Response
     */
    public function __invoke(Request $request, Response $response, callable $next): Response
    {
        $uri = $request->getUri();
        $path = $uri->getPath();

        if ('/' != $path && '/' == substr($path, -1)) {
            while ('/' == substr($path, -1)) {
                $path = substr($path, 0, -1);
            }

            $uri = $uri->withPath($path);

            if ('GET' == $request->getMethod()) {
                return $response->withRedirect($uri, 301);
            }

            $request = $request->withUri($uri);
        }

        return $next($request, $response);
    }
}
