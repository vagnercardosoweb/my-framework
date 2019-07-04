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
 * Class OldParamMiddleware.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class OldParamMiddleware extends Middleware
{
    /**
     * @param \Psr\Http\Message\RequestInterface|\Slim\Http\Request $request  PSR7 request
     * @param \Psr\Http\Message\ResponseInterface                   $response PSR7 response
     * @param callable                                              $next     Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        if (!$request->isXhr()) {
            $this->view->addGlobal('oldParam', filter_values($request->getParams()));
        }

        return $next($request, $response);
    }
}
