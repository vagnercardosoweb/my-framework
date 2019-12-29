<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 29/12/2019 Vagner Cardoso
 */

namespace App\Middlewares;

use Psr\Http\Message\ResponseInterface;
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
        'X-GitHub-Delivery',
        'X-GitHub-Event',
        'X-Hub-Signature',
        'X-Gitlab-Token',
        'X-Gitlab-Event',
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
     * @param \Slim\Http\Request  $request  PSR7 request
     * @param \Slim\Http\Response $response PSR7 response
     * @param callable            $next     Next middleware
     *
     * @return \Slim\Http\Response
     */
    public function __invoke(Request $request, Response $response, callable $next): Response
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
