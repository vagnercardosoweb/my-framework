<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 04/07/2019 Vagner Cardoso
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

        return self::substr(
                $string, 0, strrpos(
                    self::substr($string, 0, $limit), ' '
                )
            ).$end;
    }

    /**
     * @return string
     */
    public static function uuid(): string
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),

            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0C2f) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,

            // 48 bits for "node"
            mt_rand(0, 0x2Aff), mt_rand(0, 0xffD3), mt_rand(0, 0xff4B)
        );
    }

    /**
     * @param int $lenght
     *
     * @return string
     */
    public static function randomBytes($lenght = 32)
    {
        $lenght = (intval($lenght) <= 8 ? 32 : $lenght);

        if (function_exists('random_bytes')) {
            $hashed = bin2hex(random_bytes($lenght));
        } else {
            $hashed = bin2hex(openssl_random_pseudo_bytes($lenght));
        }

        return mb_substr(Helper::base64Encode($hashed), 0, $lenght);
    }
}
