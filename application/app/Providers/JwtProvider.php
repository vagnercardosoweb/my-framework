<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 04/07/2019 Vagner Cardoso
 */

namespace App\Providers;

use Core\Jwt;

/**
 * Class JwtProvider.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class JwtProvider extends Provider
{
    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function register(): void
    {
        $this->container['jwt'] = function () {
            return new Jwt(
                (env('APP_KEY', null)
                    ?: md5(md5(
                            sprintf('vcw-%s', $_SERVER['HTTP_HOST'] ?? 'VCWebNetworks'))
                    ))
            );
        };
    }
}
