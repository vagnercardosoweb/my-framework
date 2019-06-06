<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author    Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @copyright 31/05/2019 Vagner Cardoso
 */

namespace App\Middlewares {
    use Slim\Http\Request;
    use Slim\Http\Response;

    /**
     * Class TrailingSlashMiddleware
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
        public function __invoke(Request $request, Response $response, callable $next)
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
}
