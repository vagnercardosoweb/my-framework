<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 13/02/2020 Vagner Cardoso
 */

namespace Core\Password;

/**
 * Class PasswordFactory.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
final class PasswordFactory
{
    /**
     * @param string|null $hash
     *
     * @return \Core\Password\Password
     */
    public static function create(string $hash = null): Password
    {
        if ('argon' === $hash) {
            return new Argon();
        }

        if ('argon2id' === $hash) {
            return new Argon2Id();
        }

        return new Bcrypt();
    }
}
