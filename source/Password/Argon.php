<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 25/01/2021 Vagner Cardoso
 */

namespace Core\Password;

/**
 * Class Argon.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class Argon extends Bcrypt
{
    /**
     * @return int|string
     */
    public function algorithm()
    {
        return PASSWORD_ARGON2I;
    }

    /**
     * @param array $options
     *
     * @return array
     */
    protected function getOptions(array $options = []): array
    {
        return [
            'memory_cost' => $options['memory_cost'] ?? PASSWORD_ARGON2_DEFAULT_MEMORY_COST,
            'time_cost' => $options['time_cost'] ?? PASSWORD_ARGON2_DEFAULT_TIME_COST,
            'threads' => $options['threads'] ?? PASSWORD_ARGON2_DEFAULT_THREADS,
        ];
    }
}
