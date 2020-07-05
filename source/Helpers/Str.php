<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 05/07/2020 Vagner Cardoso
 */

namespace Core\Helpers;

/**
 * Class Str.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class Str extends \Illuminate\Support\Str
{
    /**
     * @param string $string
     * @param int    $limit
     * @param string $end
     *
     * @return string
     */
    public static function chars(?string $string, ?int $limit = 50, ?string $end = '...'): ?string
    {
        if (strlen($string) <= $limit) {
            return $string;
        }

        $length = strrpos(self::substr($string, 0, $limit), ' ');

        return self::substr($string, 0, $length).$end;
    }

    /**
     * @param string|int $value
     *
     * @return string|null
     */
    public static function removeSpaces($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        return trim(preg_replace('/\s+/', '', $value));
    }

    /**
     * @throws \Exception
     *
     * @return string
     */
    public static function uuid(): string
    {
        $uuid = bin2hex(random_bytes(16));

        return sprintf('%08s-%04s-%04x-%04x-%012s',
            // 32 bits for "time_low"
            substr($uuid, 0, 8),
            // 16 bits for "time_mid"
            substr($uuid, 8, 4),
            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            hexdec(substr($uuid, 12, 3)) & 0x0fff | 0x4000,
            // 16 bits:
            // * 8 bits for "clk_seq_hi_res",
            // * 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            hexdec(substr($uuid, 16, 4)) & 0x3fff | 0x8000,
            // 48 bits for "node"
            substr($uuid, 20, 12)
        );
    }

    /**
     * @param int $length
     *
     * @throws \Exception
     *
     * @return string
     */
    public static function randomBytes($length = 32)
    {
        $length = (intval($length) <= 8 ? 32 : $length);

        if (function_exists('random_bytes')) {
            $hashed = bin2hex(random_bytes($length));
        } else {
            $hashed = bin2hex(openssl_random_pseudo_bytes($length));
        }

        return mb_substr($hashed, 0, $length);
    }
}
