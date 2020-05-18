<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 26/02/2020 Vagner Cardoso
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
            throw new \InvalidArgumentException('Jwt empty key.');
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
     * @param string $value
     * @param string $algorithm
     *
     * @return string
     */
    private function signature(string $value, string $algorithm = 'HS256'): string
    {
        if (!array_key_exists($algorithm, $this->algorithms)) {
            throw new \InvalidArgumentException(
                "Algorithm {$algorithm} is not supported."
            );
        }

        list($function, $algorithm) = $this->algorithms[$algorithm];

        switch ($function) {
            case 'hash_hmac':
                return hash_hmac($algorithm, $value, $this->key, true);
                break;
        }
    }

    /**
     * @param string $token
     *
     * @return array
     * @throws \Exception
     *
     */
    public function decode(string $token): array
    {
        $split = explode('.', $token);

        if (3 != count($split)) {
            throw new \InvalidArgumentException('The token does not contain a valid format.');
        }

        // Separate the token
        list($header64, $payload64, $signature) = $split;

        if (!$header = json_decode(base64_decode($header64), true, 512, JSON_BIGINT_AS_STRING)) {
            throw new \UnexpectedValueException('Invalid header encoding.');
        }

        if (!$payload = json_decode(base64_decode($payload64), true, 512, JSON_BIGINT_AS_STRING)) {
            throw new \UnexpectedValueException('Invalid payload encoding.');
        }

        if (!$signature = base64_decode($signature)) {
            throw new \UnexpectedValueException('Invalid signature encoding.');
        }

        if (empty($header['alg'])) {
            throw new \UnexpectedValueException('Empty algorithm.');
        }

        if (!array_key_exists($header['alg'], $this->algorithms)) {
            throw new \UnexpectedValueException("Algorithm {$header['alg']} is not supported.");
        }

        if (!$this->validate("{$header64}.{$payload64}", $signature, $header['alg'])) {
            throw new \Exception('Signature verification failed.');
        }

        return $payload;
    }

    /**
     * @param string $value
     * @param string $signature
     * @param string $algorithm
     *
     * @return bool
     */
    private function validate(string $value, string $signature, string $algorithm = 'HS256'): bool
    {
        if (!array_key_exists($algorithm, $this->algorithms)) {
            throw new \InvalidArgumentException("Algorithm {$algorithm} is not supported.");
        }

        list($function, $algorithm) = $this->algorithms[$algorithm];

        switch ($function) {
            case 'hash_hmac':
                $hashed = hash_hmac($algorithm, $value, $this->key, true);

                if (function_exists('hash_equals')) {
                    return hash_equals($signature, $hashed);
                }

                return $signature === $hashed;
                break;
        }

        return false;
    }
}
