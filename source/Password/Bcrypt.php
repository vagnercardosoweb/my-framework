<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 02/11/2019 Vagner Cardoso
 */

namespace Core\Password;

/**
 * Class Bcrypt.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class Bcrypt extends Password
{
    /**
     * @return int
     */
    public function algorithm(): int
    {
        return PASSWORD_BCRYPT;
    }

    /**
     * @param array $options
     *
     * @return array
     */
    protected function getOptions(array $options): array
    {
        return [
            'cost' => $options['cost'] ?? PASSWORD_BCRYPT_DEFAULT_COST,
        ];
    }
}
