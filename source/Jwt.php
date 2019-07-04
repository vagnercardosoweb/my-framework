<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 04/07/2019 Vagner Cardoso
 */

namespace Core;

/**
 * Class Jwt.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class Jwt
{
    /**
     * @var string
     */
    protected $key;

    /**
     * @var array
     */
    protected $algorithms = [
        'HS256' => ['hash_hmac', 'SHA256'],
        'HS512' => ['hash_hmac', 'SHA512'],
        'HS384' => ['hash_hmac', 'SHA384'],
    ];

    /**
     * @param string $key
     */
    public function __construct(string $key)
    {
        $this->key = (string)$key;

        if (empty($this->key)) {
            throw new \InvalidArgumentException(
                'Jwt empty key.', E_USER_ERROR
            );
        }
    }

    /**
     * @param array  $payload
     * @param string $algorithm
     * @param array  $header
     *
     * @return string
     */
    public function encode(array $payload, string $algorithm = 'HS256', array $header = []): string
    {
        $array = [];
        $header = array_merge($header, ['typ' => 'Jwt', 'alg' => $algorithm]);
        $array[] = base64_encode(json_encode($header));
        $array[] = base64_encode(json_encode($payload));
        $signature = $this->signature(implode('.', $array), $algorithm);
        $array[] = base64_encode($signature);

        return implode('.', $array);
    }

    /**
     * @param string $token
     *
     * @throws \Exception
     *
     * @return array
     */
    public function decode(string $token): array
    {
        $split = explode('.', $token);

        if (3 != count($split)) {
            throw new \InvalidArgumentException(
                'The token does not contain a valid format.', E_USER_ERROR
            );
        }

        // Separate the token
        list($header64, $payload64, $signature) = $split;

        if (!$header = json_decode(base64_decode($header64), true, 512, JSON_BIGINT_AS_STRING)) {
            throw new \UnexpectedValueException(
                'Invalid header encoding.', E_USER_ERROR
            );
        }

        if (!$payload = json_decode(base64_decode($payload64), true, 512, JSON_BIGINT_AS_STRING)) {
            throw new \UnexpectedValueException(
                'Invalid payload encoding.', E_USER_ERROR
            );
        }

        if (!$signature = base64_decode($signature)) {
            throw new \UnexpectedValueException(
                'Invalid signature encoding.', E_USER_ERROR
            );
        }

        if (empty($header['alg'])) {
            throw new \UnexpectedValueException(
                'Empty algorithm.', E_USER_ERROR
            );
        }

        if (!array_key_exists($header['alg'], $this->algorithms)) {
            throw new \UnexpectedValueException(
                "Algorithm {$header['alg']} is not supported.", E_USER_ERROR
            );
        }

        if (!$this->validate("{$header64}.{$payload64}", $signature, $header['alg'])) {
            throw new \Exception(
                'Signature verification failed.', E_USER_ERROR
            );
        }

        return $payload;
    }

    /**
     * @param string $hashed
     * @param string $algorithm
     *
     * @return string
     */
    private function signature(string $hashed, string $algorithm = 'HS256'): string
    {
        if (!array_key_exists($algorithm, $this->algorithms)) {
            throw new \InvalidArgumentException(
                "Algorithm {$algorithm} is not supported.", E_USER_ERROR
            );
        }

        list($function, $algorithm) = $this->algorithms[$algorithm];

        switch ($function) {
            case 'hash_hmac':
                return hash_hmac($algorithm, $hashed, $this->key, true);
                break;
        }
    }

    /**
     * @param string $hashed
     * @param string $signature
     * @param string $algorithm
     *
     * @return bool
     */
    private function validate(string $hashed, string $signature, string $algorithm = 'HS256'): bool
    {
        if (!array_key_exists($algorithm, $this->algorithms)) {
            throw new \InvalidArgumentException(
                "Algorithm {$algorithm} is not supported.", E_USER_ERROR
            );
        }

        list($function, $algorithm) = $this->algorithms[$algorithm];

        switch ($function) {
            case 'hash_hmac':
                $hashed = hash_hmac($algorithm, $hashed, $this->key, true);

                if (function_exists('hash_equals')) {
                    return hash_equals($signature, $hashed);
                }

                return $signature === $hashed;
                break;
        }

        return false;
    }
}
