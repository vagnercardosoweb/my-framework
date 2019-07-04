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
 * Class Argon.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class Argon extends Password
{
    /**
     * @param string $value
     * @param array  $options
     *
     * @return string
     */
    public function hash($value, array $options = []): string
    {
        $hash = password_hash(
            $value, $this->algorithm(), $this->getOptions()
        );

        if (false === $hash) {
            throw new \RuntimeException(
                'Argon2i(d) password not supported.'
            );
        }

        return $hash;
    }

    /**
     * @param string $hash
     * @param array  $options
     *
     * @return bool
     */
    public function needsRehash(string $hash, array $options = []): bool
    {
        return password_needs_rehash(
            $hash, $this->algorithm(), $this->getOptions($options)
        );
    }

    /**
     * @return int
     */
    public function algorithm(): int
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
