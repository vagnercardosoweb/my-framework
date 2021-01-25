<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 25/01/2021 Vagner Cardoso
 */

namespace Core\Helpers;

/**
 * Class Base64.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class Base64
{
    /**
     * @param string $data
     *
     * @return string
     */
    public static function encode($data)
    {
        return str_replace(
            '=', '', strtr(
                base64_encode($data), '+/', '-_'
            )
        );
    }

    /**
     * @param string    $data
     * @param bool|null $strict
     *
     * @return bool|string
     */
    public static function decode($data, $strict = null)
    {
        $remainder = strlen($data) % 4;

        if ($remainder) {
            $padlen = 4 - $remainder;
            $data .= str_repeat('=', $padlen);
        }

        return base64_decode(
            strtr($data, '-_', '+/'), $strict
        );
    }
}
