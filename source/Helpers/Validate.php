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
 * Class Validate.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class Validate
{
    /**
     * @var array
     */
    private static $data;

    /**
     * @var string|callable
     */
    private static $rule;

    /**
     * @var string
     */
    private static $field;

    /**
     * @var array
     */
    private static $errors;

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
     * @param string|int $titleVoter
     *
     * @return bool
     */
    public static function titleVoter($titleVoter): bool
    {
        $titleVoter = str_pad(preg_replace('[^0-9]', '', $titleVoter), 12, '0', STR_PAD_LEFT);
        $uf = intval(substr($titleVoter, 8, 2));

        if (12 != strlen($titleVoter) || $uf < 1 || $uf > 28) {
            return false;
        }

        $d = 0;

        for ($i = 0; $i < 8; $i++) {
            $d += $titleVoter[$i] * (9 - $i);
        }

        $d %= 11;

        if ($d < 2) {
            if ($uf < 3) {
                $d = 1 - $d;
            } else {
                $d = 0;
            }
        } else {
            $d = 11 - $d;
        }

        if ($titleVoter[10] != $d) {
            return false;
        }

        $d *= 2;

        for ($i = 8; $i < 10; $i++) {
            $d += $titleVoter[$i] * (12 - $i);
        }

        $d %= 11;

        if ($d < 2) {
            if ($uf < 3) {
                $d = 1 - $d;
            } else {
                $d = 0;
            }
        } else {
            $d = 11 - $d;
        }

        if ($titleVoter[11] != $d) {
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
    public static function length($value, int $length): bool
    {
        return strlen($value) == $length;
    }

    /**
     * @param mixed $value
     * @param int   $length
     *
     * @return bool
     */
    public static function minLength($value, int $length): bool
    {
        return strlen($value) >= $length;
    }

    /**
     * @param mixed $value
     * @param int   $length
     *
     * @return bool
     */
    public static function maxLength($value, int $length): bool
    {
        return strlen($value) <= $length;
    }

    /**
     * @param mixed $value
     * @param mixed $min
     * @param mixed $max
     * @param bool  $length
     *
     * @return bool
     */
    public static function between($value, $min = PHP_INT_MIN, $max = PHP_INT_MAX, bool $length = false): bool
    {
        if ($length) {
            $value = strlen($value);
        }

        if (!$length && '0' == substr($value, 0, 1)) {
            while ('0' == substr($value, 0, 1)) {
                $value = substr($value, 1);
            }
        }

        if (!is_numeric($value)) {
            throw new \InvalidArgumentException(
                sprintf('%s: value must be an integer', self::$field)
            );
        }

        return filter_var($value, is_int($value) ? FILTER_VALIDATE_INT : FILTER_VALIDATE_FLOAT, [
            'options' => ['min_range' => $min, 'max_range' => $max],
        ]);
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
     * @param mixed $value
     * @param mixed $repeat
     *
     * @return bool
     */
    public static function equals($value, $repeat): bool
    {
        if (!empty(self::$data[$repeat])) {
            $repeat = self::$data[$repeat];
        }

        return self::comparison($value, '=', $repeat);
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
                return $value1 <= $value2;
            case '>=':
                return $value1 >= $value2;
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
     * @param string $regex
     *
     * @return bool
     */
    public static function notRegex($value, string $regex): bool
    {
        return !self::regex($value, $regex);
    }

    /**
     * @param mixed  $value
     * @param string $regex
     *
     * @return bool
     */
    public static function regex($value, string $regex): bool
    {
        if (!is_string($value) && !is_numeric($value)) {
            return false;
        }

        return preg_match($regex, $value) > 0;
    }

    /**
     * @param string|int|float $value
     *
     * @return bool
     */
    public static function numeric($value): bool
    {
        return is_numeric($value);
    }

    /**
     * @param mixed       $value
     * @param string      $table
     * @param string|null $field
     * @param string|null $where
     * @param string|null $driver
     *
     * @return bool
     */
    public static function databaseNotExists(
        $value,
        string $table,
        ?string $field = null,
        ?string $where = null,
        ?string $driver = null
    ): bool {
        return !self::databaseExists($value, $table, $field, $where, $driver);
    }

    /**
     * @param mixed       $value
     * @param string      $table
     * @param string|null $where
     * @param string|null $field
     * @param string|null $driver
     *
     * @return bool
     */
    public static function databaseExists(
        $value,
        string $table,
        ?string $field = null,
        ?string $where = null,
        ?string $driver = null
    ): bool {
        if (!$field) {
            $field = self::$field;
        }

        $sql = "SELECT COUNT(1) as total FROM {$table} WHERE {$table}.{$field} = :field {$where} LIMIT 1";

        return 1 == app()
            ->resolve('db')
            ->driver($driver)
            ->query($sql, ['field' => $value])
            ->fetch(\PDO::FETCH_OBJ)
            ->total;
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
     * @param string $value
     *
     * @return bool
     */
    public static function firstAndLastName(string $value): bool
    {
        return 2 === count(explode(' ', $value, 2));
    }

    /**
     * @param string $value
     *
     * @return bool
     */
    public static function phone(string $value): bool
    {
        $phone = Helper::onlyNumber($value);
        $length = strlen($phone);

        return in_array($length, [11, 10]);
    }

    /**
     * @param array $data
     * @param array $conditions
     * @param bool  $exception
     * @param bool  $force
     *
     * @throws \Exception
     *
     * @return array|null
     */
    public static function rules(
        array &$data,
        array $conditions,
        bool $exception = true,
        bool $force = false
    ): ?array {
        self::$data = &$data;

        foreach ($conditions as $field => $rules) {
            self::$field = &$field;

            foreach ($rules as $rule => $items) {
                self::$rule = $rule;

                if (!is_array($items) || empty($items[0])) {
                    $items = [$items];
                }

                foreach ($items as $item) {
                    $validate = array_merge([
                        'code' => E_USER_ERROR,
                        'force' => $force,
                        'check' => true,
                        'params' => [],
                        'filters' => [],
                        'message' => is_string($item) ? $item : null,
                        'expected' => false,
                    ], is_array($item) ? $item : []);

                    if (!$validate['check']) {
                        continue;
                    }

                    self::forceStartFieldValue($validate);
                    self::invokableFilters($validate);

                    if (self::invokableCallable($validate, $exception) === $validate['expected']) {
                        break;
                    }
                }
            }
        }

        return self::$errors;
    }

    /**
     * @param array $validate
     */
    private static function forceStartFieldValue(array $validate): void
    {
        $data = &self::$data;
        $field = &self::$field;

        if (preg_match('/^!(?<field>.*)$/im', $field, $matches)) {
            $field = $matches['field'];
            $validate['force'] = true;
        }

        if ($validate['force'] && !isset($data[$field])) {
            $data[$field] = null;
        }
    }

    /**
     * @param array $validate
     */
    private static function invokableFilters(array $validate): void
    {
        $data = &self::$data;
        $field = self::$field;

        if (!empty($validate['filters'])) {
            foreach ($validate['filters'] as $filter) {
                $data[$field] = self::invokeCallable($filter, [$data[$field]]);
            }
        }
    }

    /**
     * @param string|callable $callable
     * @param array           $params
     *
     * @return bool
     */
    private static function invokeCallable($callable, array $params): bool
    {
        // Verify if possibility php function
        if (is_callable($callable)) {
            $method = null;

            if (is_string($callable) && false !== strpos($callable, '::')) {
                list($callable, $method) = self::parseClassMethodCallable($callable);
            }

            return self::callCallable($callable, $method, $params);
        }

        return self::callCallable(self::class, $callable, $params);
    }

    /**
     * @param array $validate
     * @param bool  $exception
     *
     * @throws \Exception
     *
     * @return bool
     */
    private static function invokableCallable(array $validate, bool $exception): bool
    {
        $data = self::$data;
        $rule = self::$rule;
        $field = self::$field;

        if (!array_key_exists($field, $data)) {
            return true;
        }

        array_unshift($validate['params'], $data[$field]);

        if (!self::invokeCallable($rule, $validate['params'])) {
            if (empty($validate['message'])) {
                $validate['message'] = "{$field} :: {$rule} return error.";
            }

            if ($exception) {
                throw new \InvalidArgumentException($validate['message'], $validate['code']);
            }

            self::$errors[$field] = [
                'code' => $validate['code'],
                'message' => $validate['message'],
            ];

            return false;
        }

        return true;
    }

    /**
     * @param string|callable $callable
     * @param string|null     $method
     * @param array           $params
     *
     * @return mixed
     */
    private static function callCallable($callable, ?string $method, array $params)
    {
        try {
            return forward_static_call_array([$callable, $method], $params);
        } catch (\Exception $e) {
            $parseCallable = is_null($method) ? $callable : [new $callable(), $method];

            return call_user_func_array($parseCallable, $params);
        }
    }

    /**
     * @param string $rule
     *
     * @return string[]
     */
    private static function parseClassMethodCallable(string $rule): array
    {
        return explode('::', $rule, 2) + [1 => '__invoke'];
    }
}
