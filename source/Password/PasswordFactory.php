<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 20/05/2020 Vagner Cardoso
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
     * @param string|null $driver
     *
     * @return \Core\Password\Password
     */
    public static function create(string $driver = null): Password
    {
        if ('argon' === $driver) {
            return new Argon();
        }

        if ('argon2id' === $driver) {
            return new Argon2Id();
        }

        return new Bcrypt();
    }
}
