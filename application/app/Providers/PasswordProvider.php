<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 26/02/2020 Vagner Cardoso
 */

namespace App\Providers;

use Core\Password\PasswordFactory;

/**
 * Class PasswordProvider.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class PasswordProvider extends Provider
{
    /**
     * @return string
     */
    public function name(): string
    {
        return 'hash';
    }

    /**
     * @return \Closure
     */
    public function register(): \Closure
    {
        return function () {
            return PasswordFactory::create(
                env('APP_PASSWORD_DRIVER', 'bcrypt')
            );
        };
    }
}
