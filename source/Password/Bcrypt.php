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
 * Class Bcrypt.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class Bcrypt extends Password
{
    /**
     * @param string $password
     * @param array  $options
     *
     * @return string
     */
    public function hash($password, array $options = []): string
    {
        $hash = password_hash(
            $password, $this->algorithm(), $this->getOptions($options)
        );

        if (false === $hash) {
            throw new \RuntimeException(
                'Bcrypt password not supported.'
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
