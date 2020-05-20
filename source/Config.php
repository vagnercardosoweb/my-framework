<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 20/05/2020 Vagner Cardoso
 */

namespace Core;

use ArrayAccess;
use Core\Helpers\Arr;
use Core\Helpers\Helper;
use Core\Helpers\Path;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Class Config.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class Config implements ArrayAccess
{
    /**
     * @var array
     */
    protected static $items = [];

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return void
     */
    public static function prepend($key, $value)
    {
        $array = self::get($key);
        array_unshift($array, $value);

        self::set($key, $array);
    }

    /**
     * @param array|string $key
     * @param mixed        $default
     *
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        if (empty(self::$items)) {
            self::$items = self::loadItems();
        }

        if (is_array($key)) {
            return self::getMany($key);
        }

        return Arr::get(self::$items, $key, $default);
    }

    /**
     * @param array $keys
     *
     * @return array
     */
    public static function getMany($keys)
    {
        $config = [];

        foreach ($keys as $key => $default) {
            if (is_numeric($key)) {
                throw new \UnexpectedValueException('the key must be a string');
            }

            $config[$key] = Arr::get(self::$items, $key, $default);
        }

        return $config;
    }

    /**
     * @param string|null $path
     *
     * @throws \Exception
     *
     * @return array
     */
    public static function loadItems(string $path = null): array
    {
        if (!is_dir($path)) {
            $path = Path::app('/config');
        }

        /** @var \DirectoryIterator $iterator */
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS));
        $iterator->rewind();

        $config = [];

        while ($iterator->valid()) {
            $directory = $iterator->getPath();
            $fileBasename = $iterator->getBasename('.php');

            if ($directory = trim(str_replace($path, '', $directory), DIRECTORY_SEPARATOR)) {
                $directory = sprintf('%s%s', $directory, DIRECTORY_SEPARATOR);
            }

            if (false !== strpos($directory, DIRECTORY_SEPARATOR)) {
                foreach (explode(DIRECTORY_SEPARATOR, $directory) as $segment) {
                    if (empty($segment) || !is_dir("{$path}/{$segment}")) {
                        continue;
                    }

                    $config[$segment] = self::loadItems("{$path}/{$segment}");
                }
            } else {
                $config[$fileBasename] = require_once "{$iterator->getRealPath()}";
            }

            $iterator->next();
        }

        ksort($config, SORT_NATURAL);

        self::$items = self::normalize($config);

        return self::$items;
    }

    /**
     * @param array|string $key
     * @param mixed        $value
     *
     * @return void
     */
    public static function set($key, $value = null)
    {
        $keys = is_array($key) ? $key : [$key => $value];

        foreach ($keys as $key => $value) {
            Arr::set(self::$items, $key, $value);
        }
    }

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return void
     */
    public static function push($key, $value)
    {
        $array = self::get($key);
        $array[] = $value;

        self::set($key, $array);
    }

    /**
     * @param string     $key
     * @param mixed      $value
     * @param mixed|null $newKey
     *
     * @return void
     */
    public static function add($key, $value, $newKey)
    {
        $array = self::get($key);
        $array[$newKey] = $value;

        self::set($key, $array);
    }

    /**
     * @return array
     */
    public static function all()
    {
        return self::$items;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function offsetExists($key)
    {
        return self::exists($key);
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function exists($key)
    {
        return Arr::has(self::$items, $key);
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function offsetGet($key)
    {
        return self::get($key);
    }

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return void
     */
    public function offsetSet($key, $value)
    {
        self::set($key, $value);
    }

    /**
     * @param string $key
     *
     * @return void
     */
    public function offsetUnset($key)
    {
        self::set($key, null);
    }

    /**
     * @param array $config
     *
     * @return array
     */
    protected static function normalize(array $config): array
    {
        foreach ($config as $key => $value) {
            if (is_array($value)) {
                $config[$key] = self::normalize($value);
            } else {
                $config[$key] = Helper::normalizeValueType($value);
            }
        }

        return $config;
    }
}
