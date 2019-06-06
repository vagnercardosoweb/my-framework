<?php

/**
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>.
 *
 * @author    Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @copyright 31/05/2019 Vagner Cardoso
 */

namespace App\Providers {
    use Core\Jwt;

    /**
     * Class JwtProvider.
     *
     * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
     */
    class JwtProvider extends Provider
    {
        public function register()
        {
            $this->container['jwt'] = function () {
                return new Jwt(
                    (env('APP_KEY') ?? md5(md5('VCWEBNETWORKS')))
                );
            };
        }
    }
}
