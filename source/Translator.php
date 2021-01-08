<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 08/01/2021 Vagner Cardoso
 */

namespace Core;

use Core\Helpers\Arr;
use Core\Helpers\Path;

/**
 * Class Translator.
 */
class Translator
{
    /**
     * @var array
     */
    protected static $data = [];

    /**
     * @var string
     */
    protected static $language = null;

    /**
     * @var string
     */
    protected static $fallback = 'en';

    /**
     * @param string $fallback
     *
     * @throws \Exception
     */
    public static function setFallback(string $fallback): void
    {
        self::$fallback = self::resolveLanguageName($fallback);
    }

    /**
     * @return string
     */
    public static function getFallback(): string
    {
        return self::$fallback;
    }

    /**
     * @param string $language
     *
     * @throws \Exception
     */
    public static function setLanguage(string $language): void
    {
        self::$language = self::parseLanguageName($language);
    }

    /**
     * @return string
     */
    public static function getLanguage(): string
    {
        if (self::$language) {
            return self::$language;
        }

        return self::$fallback;
    }

    /**
     * @param string $key
     *
     * @throws \Exception
     *
     * @return string|array
     */
    public static function get(string $key)
    {
        list($file, $key) = explode('.', $key, 2);
        self::loadData($file);

        $value = 'unknown';
        $language = self::$language;
        $fallback = self::$fallback;
        $translated = Arr::get(self::$data[$file], $key, null);

        if (!$translated) {
            return "message {$value}";
        }

        $args = func_get_args();
        array_shift($args);

        if (!empty($args[0])) {
            if (!empty($args[0][$language])) {
                $value = $args[0][$language];
            } elseif (!empty($args[0][$fallback])) {
                $value = $args[0][$fallback];
            }
        }

        $translated = str_replace(':language:', $value, $translated);

        if (
            !empty($args)
            && !is_array($translated)
            && false !== strpos($translated, '%')
        ) {
            try {
                if (is_array($args[0])) {
                    $args = $args[0]['args'] ?? $args[0];
                }

                return sprintf($translated, ...$args);
            } catch (\Exception $e) {
            }
        }

        return $translated;
    }

    /**
     * @param string $file
     */
    protected static function loadData(string $file): void
    {
        if (!empty(self::$data[$file])) {
            return;
        }

        self::$data[$file] = [];

        $path =
            self::existsFile(self::$language, $file) ??
            self::existsFile(self::$fallback, $file);

        if (!$path) {
            return;
        }

        self::$data[$file] = require "{$path}";
    }

    /**
     * @param string $language
     *
     * @return string|null
     */
    protected static function existsFolder(string $language): ?string
    {
        $path = sprintf(Path::resource('/languages/%s'), $language);

        if (!is_dir($path)) {
            return null;
        }

        return $path;
    }

    /**
     * @param string $language
     * @param string $file
     *
     * @return string|null
     */
    protected static function existsFile(string $language, string $file): ?string
    {
        $path = sprintf(Path::resource('/languages/%s/%s.php'), $language, $file);

        if (!file_exists($path)) {
            return null;
        }

        return $path;
    }

    /**
     * @param string $header
     *
     * @return string
     */
    protected static function parseLanguageName(string $header): string
    {
        $splitHeader = explode(',', $header);
        $splitHeader = array_map(function (string $name) {
            list($name) = explode(';', $name);

            return trim($name);
        }, $splitHeader);

        foreach ($splitHeader as $language) {
            $language = self::resolveLanguageName($language);

            if ('*' === $language) {
                break;
            }

            if (self::existsFolder($language)) {
                return $language;
            }
        }

        return self::$fallback;
    }

    /**
     * @param string $language
     *
     * @return string
     */
    protected static function resolveLanguageName(string $language): string
    {
        return str_replace('_', '-', strtolower($language));
    }
}
