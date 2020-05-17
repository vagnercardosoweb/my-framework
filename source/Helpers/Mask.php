<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 13/02/2020 Vagner Cardoso
 */

namespace Core\Helpers;

/**
 * Class Mask.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class Mask
{
    const MASK_CPF = '###.###.###-##';

    const MASK_CNPJ = '##.###.###/####-##';

    const MASK_PHONE = ['(##)####-####', '(##)#####-####'];

    const MASK_CEP = '##.###-###';

    /**
     * @param string $value
     * @param string $mask
     *
     * @return string
     */
    public static function create($value, string $mask): string
    {
        $newValue = self::remove($value);
        $valueLength = strlen($newValue);
        $maskLength = strlen(preg_replace('/[^#]/m', '', $mask));
        $maskCalculateLength = $maskLength - $valueLength;

        if ($maskCalculateLength > 0) {
            $mask = substr($mask, 0, -$maskCalculateLength);
        }

        if ($valueLength > $maskLength) {
            return $value;
        }

        return vsprintf(
            str_replace('#', '%s', $mask),
            str_split($newValue)
        );
    }

    /**
     * @param string $value
     *
     * @return string
     */
    public static function remove($value): string
    {
        return preg_replace(
            '/[\-\|\(\)\/\.\: ]/',
            '',
            $value
        );
    }

    /**
     * @param string $value
     *
     * @return string
     */
    public static function cep($value): string
    {
        return self::create(
            Helper::onlyNumber($value),
            self::MASK_CEP
        );
    }

    /**
     * @param string $value
     *
     * @return string
     */
    public static function phone($value): string
    {
        $value = Helper::onlyNumber($value);
        $mask = 10 == strlen($value) ? self::MASK_PHONE[0] : self::MASK_PHONE[1];

        return self::create($value, $mask);
    }

    /**
     * @param string $value
     *
     * @return string
     */
    public static function cpfOrCnpj($value): string
    {
        if (11 === strlen($value)) {
            return self::cpf($value);
        }

        return self::cnpj($value);
    }

    /**
     * @param string $value
     *
     * @return string
     */
    public static function cpf($value): string
    {
        return self::create(
            Helper::onlyNumber($value),
            self::MASK_CPF
        );
    }

    /**
     * @param string $value
     *
     * @return string
     */
    public static function cnpj($value): string
    {
        return self::create(
            Helper::onlyNumber($value),
            self::MASK_CNPJ
        );
    }
}
