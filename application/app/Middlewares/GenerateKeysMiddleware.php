<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 05/03/2020 Vagner Cardoso
 */

namespace App\Middlewares;

use Core\Env;
use Core\Helpers\Helper;
use Core\Helpers\Path;
use Core\Helpers\Str;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class GenerateKeysMiddleware.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class GenerateKeysMiddleware extends Middleware
{
    /**
     * @param \Slim\Http\Request  $request  PSR7 request
     * @param \Slim\Http\Response $response PSR7 response
     * @param callable            $next     Next middleware
     *
     * @throws \Exception
     *
     * @return \Slim\Http\Response
     */
    public function __invoke(Request $request, Response $response, callable $next): Response
    {
        foreach (['APP_KEY', 'API_KEY', 'DEPLOY_KEY'] as $key) {
            $value = Env::get($key, null);
            $value = Helper::normalizeValueType($value);
            $pathEnv = Path::app('/.env');

            if (empty($value) && !file_exists($pathEnv)) {
                $quote = preg_quote("={$value}", '/');
                $random = Str::randomBytes(32);

                file_put_contents(
                    $pathEnv,
                    preg_replace(
                        "/^{$key}{$quote}.*/m",
                        "{$key}=vcw:{$random}",
                        file_get_contents($pathEnv)
                    )
                );
            }
        }

        return $next($request, $response);
    }
}
