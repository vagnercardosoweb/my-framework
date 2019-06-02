<?php

/**
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
     * Class MaintenanceMiddleware
     *
     * @package App\Middlewares
     * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
     */
    class MaintenanceMiddleware extends Middleware
    {
        /**
         * @param \Slim\Http\Request $request PSR7 request
         * @param \Slim\Http\Response $response PSR7 response
         * @param callable $next Next middleware
         *
         * @return \Slim\Http\Response
         * @throws \Exception
         */
        public function __invoke(Request $request, Response $response, callable $next)
        {
            if (env('APP_MAINTENANCE', false) == 'true') {
                return $this->view->render(
                    $response, '@error.503', [], 503
                );
            }

            $response = $next($request, $response);

            return $response;
        }
    }
}
