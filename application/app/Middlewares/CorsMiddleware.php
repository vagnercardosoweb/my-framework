<?php

/**
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>.
 *
 * @author    Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @copyright 31/05/2019 Vagner Cardoso
 */

namespace App\Middlewares {
    use Slim\Http\Request;
    use Slim\Http\Response;

    /**
     * Class CorsMiddleware.
     *
     * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
     */
    class CorsMiddleware extends Middleware
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
            /** @var Response $response */
            $response = $next($request, $response);

            /*header_remove("Cache-Control");
            header_remove("Expires");
            header_remove("Pragma");*/

            return $response->withHeader('Access-Control-Allow-Origin', '*')
                ->withHeader('Access-Control-Allow-Headers', implode(', ', [
                    'X-Requested-With',
                    'X-Http-Method-Override',
                    'Content-Type',
                    'Accept',
                    'Origin',
                    'Authorization',
                    'X-Csrf-Token',
                ]))
                ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS')
            ;
        }
    }
}
