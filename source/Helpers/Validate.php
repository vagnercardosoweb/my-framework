<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 18/06/2019 Vagner Cardoso
 */

namespace Core\Helpers;

/**
 * Class Validate.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class Validate
{
    /**
     * @param string $mail
     *
     * @return bool
     */
    public static function mail(string $mail): bool
    {
        $mail = filter_var((string)$mail, FILTER_SANITIZE_EMAIL);
        $regex = '/[a-z0-9_\.\-]+@[a-z0-9_\.\-]*[a-z0-9_\.\-]+\.[a-z]{2,4}$/';

        if (filter_var($mail, FILTER_VALIDATE_EMAIL) && preg_match($regex, $mail)) {
            return true;
        }

        return false;
    }

    /**
     * @param string|int $cpf
     *
     * @return bool
     */
    public static function cpf($cpf): bool
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);

        if (11 != strlen($cpf)) {
            return false;
        }

        $digitA = 0;
        $digitB = 0;

        for ($i = 0, $x = 10; $i <= 8; $i++, $x--) {
            $digitA += $cpf[$i] * $x;
        }

        for ($i = 0, $x = 11; $i <= 9; $i++, $x--) {
            if (str_repeat($i, 11) == $cpf) {
                return false;
            }

            $digitB += $cpf[$i] * $x;
        }

        $sumA = (($digitA % 11) < 2) ? 0 : 11 - ($digitA % 11);
        $sumB = (($digitB % 11) < 2) ? 0 : 11 - ($digitB % 11);

        if ($sumA != $cpf[9] || $sumB != $cpf[10]) {
            return false;
        }

        return true;
    }

    /**
     * @param string|int $cnpj
     *
     * @return bool
     */
    public static function cnpj($cnpj): bool
    {
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);

        if (14 != strlen($cnpj)) {
            return false;
        }

        $digitA = 0;
        $digitB = 0;

        for ($i = 0, $c = 5; $i <= 11; $i++, $c--) {
            $c = (1 == $c ? 9 : $c);
            $digitA += $cnpj[$i] * $c;
        }

        for ($i = 0, $c = 6; $i <= 12; $i++, $c--) {
            if (str_repeat($i, 14) == $cnpj) {
                return false;
            }

            $c = (1 == $c ? 9 : $c);
            $digitB += $cnpj[$i] * $c;
        }

        $sumA = (($digitA % 11) < 2) ? 0 : 11 - ($digitA % 11);
        $sumB = (($digitB % 11) < 2) ? 0 : 11 - ($digitB % 11);

        if (14 != strlen($cnpj)) {
            return false;
        }
        if ($sumA != $cnpj[12] || $sumB != $cnpj[13]) {
            return false;
        }

        return true;
    }
}
