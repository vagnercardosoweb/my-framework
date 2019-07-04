<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 04/07/2019 Vagner Cardoso
 */

namespace App\Middlewares;

use Core\Helpers\Str;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class GenerateKeysMiddleware.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class GenerateKeysMiddleware extends Middleware
{
    /**
     * @param \Psr\Http\Message\RequestInterface  $request  PSR7 request
     * @param \Psr\Http\Message\ResponseInterface $response PSR7 response
     * @param callable                            $next     Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        foreach (['APP_KEY', 'API_KEY', 'DEPLOY_KEY'] as $key) {
            $value = env($key, '');

            if (empty($value)) {
                $random = Str::randomBytes(32);
                $scaped = preg_quote("={$value}", '/');

                file_put_contents(
                    APP_FOLDER.'/.env',
                    preg_replace(
                        "/^{$key}{$scaped}/m",
                        "{$key}=vcw_{$random}",
                        file_get_contents(APP_FOLDER.'/.env')
                    )
                );
            }
        }

        return $next($request, $response);
    }
}
