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
     * Class OldInputMiddleware
     *
     * @package App\Middlewares
     * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
     */
    class OldInputMiddleware extends Middleware
    {
        /**
         * @param \Slim\Http\Request $request PSR7 request
         * @param \Slim\Http\Response $response PSR7 response
         * @param callable $next Next middleware
         *
         * @return \Slim\Http\Response
         */
        public function __invoke(Request $request, Response $response, callable $next)
        {
            if (!$request->isXhr()) {
                $this->view->addGlobal('_input', request_params());
            }

            $response = $next($request, $response);

            return $response;
        }
    }
}
