<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 04/07/2019 Vagner Cardoso
 */

namespace App\Middlewares;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class TrailingSlashMiddleware.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class TrailingSlashMiddleware extends Middleware
{
    /**
     * @param \Psr\Http\Message\RequestInterface                      $request  PSR7 request
     * @param \Psr\Http\Message\ResponseInterface|\Slim\Http\Response $response PSR7 response
     * @param callable                                                $next     Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        $uri = $request->getUri();
        $path = $uri->getPath();

        if ('/' != $path && '/' == substr($path, -1)) {
            $uri = $uri->withPath(substr($path, 0, -1));

            if ('GET' == $request->getMethod()) {
                return $response->withRedirect((string)$uri, 301);
            }

            return $next($request->withUri($uri), $response);
        }

        return $next($request, $response);
    }
}
