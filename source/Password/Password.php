<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 08/01/2021 Vagner Cardoso
 */

namespace Core\Password;

/**
 * Class Password.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
abstract class Password
{
    /**
     * @param string $hash
     *
     * @return array
     */
    public function info(string $hash): array
    {
        return password_get_info($hash);
    }

    /**
     * @param string $password
     * @param string $hash
     *
     * @return bool
     */
    public function verify($password, string $hash): bool
    {
        if (0 === strlen($hash)) {
            return false;
        }

        return password_verify($password, $hash);
    }

    /**
     * @param string|int $password
     * @param array      $options
     *
     * @return string
     */
    public function make($password, array $options = []): string
    {
        $hashed = password_hash(
            $password, $this->algorithm(), $this->getOptions($options)
        );

        if (false === $hashed) {
            throw new \RuntimeException(
                sprintf('%s password not supported.', __CLASS__)
            );
        }

        return $hashed;
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
     * @return int|string
     */
    abstract protected function algorithm();

    /**
     * @param array $options
     *
     * @return array
     */
    abstract protected function getOptions(array $options): array;
}
