<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 04/07/2019 Vagner Cardoso
 */

namespace Core\Helpers;

use Core\App;

/**
 * Class Validate.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class Validate
{
    /**
     * @var array
     */
    private static $data = [];

    /**
     * @param string $value
     *
     * @return bool
     */
    public static function email(string $value): bool
    {
        $value = filter_var((string)$value, FILTER_SANITIZE_EMAIL);
        $regex = '/[a-z0-9_\.\-]+@[a-z0-9_\.\-]*[a-z0-9_\.\-]+\.[a-z]{2,4}$/';

        if (filter_var($value, FILTER_VALIDATE_EMAIL) && preg_match($regex, $value)) {
            return true;
        }

        return false;
    }

    /**
     * @param string|int $value
     *
     * @return bool
     */
    public static function cpf($value): bool
    {
        $value = preg_replace('/[^0-9]/', '', $value);

        if (11 != strlen($value)) {
            return false;
        }

        $digitA = 0;
        $digitB = 0;

        for ($i = 0, $x = 10; $i <= 8; $i++, $x--) {
            $digitA += $value[$i] * $x;
        }

        for ($i = 0, $x = 11; $i <= 9; $i++, $x--) {
            if (str_repeat($i, 11) == $value) {
                return false;
            }

            $digitB += $value[$i] * $x;
        }

        $sumA = (($digitA % 11) < 2) ? 0 : 11 - ($digitA % 11);
        $sumB = (($digitB % 11) < 2) ? 0 : 11 - ($digitB % 11);

        if ($sumA != $value[9] || $sumB != $value[10]) {
            return false;
        }

        return true;
    }

    /**
     * @param string|int $value
     *
     * @return bool
     */
    public static function cnpj($value): bool
    {
        $value = preg_replace('/[^0-9]/', '', $value);

        if (14 != strlen($value)) {
            return false;
        }

        $digitA = 0;
        $digitB = 0;

        for ($i = 0, $c = 5; $i <= 11; $i++, $c--) {
            $c = (1 == $c ? 9 : $c);
            $digitA += $value[$i] * $c;
        }

        for ($i = 0, $c = 6; $i <= 12; $i++, $c--) {
            if (str_repeat($i, 14) == $value) {
                return false;
            }

            $c = (1 == $c ? 9 : $c);
            $digitB += $value[$i] * $c;
        }

        $sumA = (($digitA % 11) < 2) ? 0 : 11 - ($digitA % 11);
        $sumB = (($digitB % 11) < 2) ? 0 : 11 - ($digitB % 11);

        if (14 != strlen($value)) {
            return false;
        }
        if ($sumA != $value[12] || $sumB != $value[13]) {
            return false;
        }

        return true;
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    public static function required($value): bool
    {
        if (is_null($value)) {
            return false;
        }

        if (is_string($value) && '' === trim($value)) {
            return false;
        }

        if (is_array($value) && count($value) < 1) {
            return false;
        }

        return true;
    }

    /**
     * @param string $xml
     *
     * @return \SimpleXMLElement|null
     */
    public static function xml(string $xml): ?\SimpleXMLElement
    {
        $xml = trim($xml);

        if (empty($xml)) {
            return null;
        }

        if (false !== stripos($xml, '<!DOCTYPE html>')) {
            return null;
        }

        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($xml);
        $errors = libxml_get_errors();
        libxml_clear_errors();

        if (!empty($errors)) {
            return null;
        }

        return $xml;
    }

    /**
     * @param string $json
     * @param bool   $assoc
     * @param int    $depth
     * @param int    $options
     *
     * @return object|array|bool
     */
    public static function json(
        string $json,
        bool $assoc =
        false,
        int $depth = 512,
        int $options = 0
    ) {
        $json = json_decode($json, $assoc, $depth, $options);

        if (JSON_ERROR_NONE !== json_last_error()) {
            return false;
        }

        return $json;
    }

    /**
     * @param array|object $array
     *
     * @return bool
     */
    public static function emptyArrayRecursive($array): bool
    {
        $array = Obj::toArray($array);

        if (empty($array)) {
            return true;
        }

        foreach ((array)$array as $key => $value) {
            if (is_array($value)) {
                return self::emptyArrayRecursive($value);
            }

            if (empty($value) && '0' != $value) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    public static function numeric($value): bool
    {
        return is_numeric($value);
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    public static function string($value): bool
    {
        return is_string($value);
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    public static function array($value): bool
    {
        return is_array($value);
    }

    /**
     * @param mixed $value
     * @param array $array
     * @param bool  $strict
     *
     * @return bool
     */
    public static function inArray($value, array $array, bool $strict = false): bool
    {
        return in_array($value, $array, $strict);
    }

    /**
     * @param mixed $value
     * @param int   $length
     *
     * @return bool
     */
    public static function length($value, $length): bool
    {
        return strlen($value) == $length;
    }

    /**
     * @param mixed $value
     * @param int   $length
     *
     * @return bool
     */
    public static function minLength($value, $length): bool
    {
        return strlen($value) >= $length;
    }

    /**
     * @param mixed $value
     * @param int   $length
     *
     * @return bool
     */
    public static function maxLength($value, $length): bool
    {
        return strlen($value) <= $length;
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    public static function ip($value): bool
    {
        return false !== filter_var($value, FILTER_VALIDATE_IP);
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    public static function ipv4($value): bool
    {
        return false !== filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    public static function ipv6($value): bool
    {
        return false !== filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    public static function boolean($value): bool
    {
        $boolean = [0, 1, '0', '1', true, false, 'true', 'false'];

        return in_array($value, $boolean, true);
    }

    /**
     * @param mixed $value
     * @param mixed $value2
     *
     * @return bool
     */
    public static function isSameType($value, $value2): bool
    {
        return gettype($value) == gettype($value2);
    }

    /**
     * @param mixed  $value1
     * @param string $operator
     * @param mixed  $value2
     *
     * @return bool
     */
    public static function comparison($value1, string $operator, $value2): bool
    {
        switch ($operator) {
            case '<':
                return $value1 < $value2;
            case '>':
                return $value1 > $value2;
            case '<=':
                return $value1 < $value2;
            case '>=':
                return $value1 > $value2;
            case '=':
            case '==':
                return $value1 == $value2;
            case '===':
                return $value1 === $value2;
            case '!=':
                return $value1 != $value2;
            case '!==':
                return $value1 !== $value2;
        }

        return false;
    }

    /**
     * @param mixed  $value
     * @param string $indexData
     *
     * @return bool
     */
    public static function equals($value, string $indexData): bool
    {
        return self::comparison($value, '=', self::$data[$indexData]);
    }

    /**
     * @param mixed  $value
     * @param string $regex
     *
     * @return false|int
     */
    public static function regex($value, string $regex)
    {
        if (!is_string($value) && !is_numeric($value)) {
            return false;
        }

        return preg_match($regex, $value) > 0;
    }

    /**
     * @param mixed  $value
     * @param string $regex
     *
     * @return false|int
     */
    public static function notRegex($value, string $regex)
    {
        return !self::regex($value, $regex);
    }

    /**
     * @param mixed       $value
     * @param string      $table
     * @param string      $field
     * @param string|null $where
     * @param string|null $driver
     *
     * @return bool
     */
    public static function databaseExists(
        $value,
        string $table,
        string $field,
        ?string $where = null,
        ?string $driver = null
    ): bool {
        $sql = "SELECT COUNT(1) as total FROM {$table} WHERE {$table}.{$field} = :field {$where} LIMIT 1";

        return 1 == App::getInstance()
            ->resolve('db')
            ->driver($driver)
            ->query($sql, ['field' => $value])
            ->fetch(\PDO::FETCH_OBJ)
            ->total;
    }

    /**
     * @param mixed       $value
     * @param string      $table
     * @param string      $field
     * @param string|null $where
     * @param string      $driver
     *
     * @return bool
     */
    public static function databaseNotExists(
        $value,
        string $table,
        string $field,
        ?string $where = null,
        ?string $driver = null
    ): bool {
        return !self::databaseExists($value, $table, $field, $where, $driver);
    }

    /**
     * @param mixed      $value
     * @param mixed|null $options
     *
     * @return bool
     */
    public static function url($value, $options = null): bool
    {
        return false !== filter_var($value, FILTER_VALIDATE_URL, $options);
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    public static function activeUrl($value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        if ($url = parse_url($value, PHP_URL_HOST)) {
            try {
                return count(dns_get_record($url, DNS_A | DNS_AAAA)) > 0;
            } catch (\Exception $e) {
                return false;
            }
        }

        return false;
    }

    /**
     * @param array|object $data
     * @param array        $conditions
     * @param bool         $exception
     *
     * @throws \Exception
     *
     * @return array
     */
    public static function rules($data, array $conditions, bool $exception = true): array
    {
        $errors = [];
        self::$data = Obj::toArray($data);

        foreach ($conditions as $field => $rules) {
            foreach ($rules as $rule => $item) {
                $validate = [
                    'force' => false,
                    'check' => true,
                    'message' => null,
                    'code' => E_USER_WARNING,
                    'params' => [],
                ];

                // Check params
                if (isset($item['params'])) {
                    foreach ((array)$item['params'] as $value) {
                        $validate['params'][] = $value;
                    }

                    unset($item['params']);
                }

                // Check message and merge rules
                if (is_string($item)) {
                    $validate['message'] = $item;
                } else {
                    $validate = array_merge($validate, $item);
                }

                if (!$validate['check']) {
                    continue;
                }

                // Check force field
                if (preg_match('/(.*)!!$/im', $field, $matches)) {
                    $field = $matches[1];
                    $validate['force'] = true;
                }

                if ($validate['force'] && !isset(self::$data[$field])) {
                    self::$data[$field] = null;
                }

                // Run validate
                if (array_key_exists($field, self::$data)) {
                    array_unshift($validate['params'], self::$data[$field]);

                    if (!self::runValidateMethod($rule, $validate['params'])) {
                        $validate['message'] = $validate['message']
                            ?? 'There is validation with undefined message return.';

                        if ($exception) {
                            throw new \Exception(
                                $validate['message'], $validate['code']
                            );
                        }

                        $errors[$field] = [
                            'code' => $validate['code'],
                            'message' => $validate['message'],
                        ];

                        break;
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * @param string $rule
     * @param array  $params
     *
     * @return bool
     */
    private static function runValidateMethod(string $rule, array $params): bool
    {
        if (false !== strpos($rule, '::')) {
            list($class, $method) = explode('::', $rule);
        } else {
            $class = self::class;
            $method = $rule;
        }

        return call_user_func_array(
            [$class, $method], $params
        );
    }
}
