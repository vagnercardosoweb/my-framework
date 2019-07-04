<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 04/07/2019 Vagner Cardoso
 */

namespace Core\Password;

/**
 * Class Argon2Id.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class Argon2Id extends Argon
{
    /**
     * @return int
     */
    public function algorithm(): int
    {
        return PASSWORD_ARGON2ID;
    }
}
