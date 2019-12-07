<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 07/12/2019 Vagner Cardoso
 */

namespace App\Middlewares;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class CorsMiddleware.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class CorsMiddleware extends Middleware
{
    /**
     * @var array
     */
    protected $allowedHeaders = [
        'Accept',
        'Origin',
        'X-Csrf-Token',
        'Content-Type',
        'Cache-Control',
        'Authorization',
        'X-Requested-With',
        'X-Http-Method-Override',
    ];

    /**
     * @var array
     */
    protected $allowedMethods = [
        'GET',
        'POST',
        'PUT',
        'DELETE',
        'PATCH',
        'OPTIONS',
    ];

    /**
     * @param \Psr\Http\Message\RequestInterface  $request  PSR7 request
     * @param \Psr\Http\Message\ResponseInterface $response PSR7 response
     * @param callable                            $next     Next middleware
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        /** @var ResponseInterface $response */
        $response = $next($request, $response);

        /*header_remove("Cache-Control");
        header_remove("Expires");
        header_remove("Pragma");*/

        return $response->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Vary', 'Origin')
            ->withHeader('Access-Control-Allow-Headers', implode(', ', $this->allowedHeaders))
            ->withHeader('Access-Control-Allow-Methods', implode(', ', $this->allowedMethods))
        ;
    }
}
