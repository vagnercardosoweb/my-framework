<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 08/06/2021 Vagner Cardoso
 */

namespace App\Providers;

use Core\Jwt;

/**
 * Class JwtProvider.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class JwtProvider extends EncryptionProvider
{
    /**
     * @return string
     */
    public function name(): string
    {
        return 'jwt';
    }

    /**
     * @return \Closure
     */
    public function register(): \Closure
    {
        return function () {
            return new Jwt($this->generateKey());
        };
    }
}
