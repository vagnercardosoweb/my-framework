<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 05/07/2020 Vagner Cardoso
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
     * @return int|string
     */
    public function algorithm()
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
