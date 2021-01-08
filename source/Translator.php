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
     * @param string $language
     *
     * @throws \Exception
     */
    public static function setFallback(string $language): void
    {
        self::$fallback = self::parseLanguage($language);
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
        self::$language = self::parseLanguage($language);
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

        $language = self::$language;
        $languagePath = Path::resource('/languages/%s/%s.php');

        if (!file_exists(sprintf($languagePath, $language, $file))) {
            $language = self::$fallback;
        }

        if (!file_exists(sprintf($languagePath, $language, $file))) {
            return;
        }

        $languagePath = sprintf($languagePath, $language, $file);

        self::$data[$file] = require "{$languagePath}";
    }

    /**
     * @param string $language
     *
     * @return string
     */
    protected static function parseLanguage(string $language): string
    {
        return str_replace('_', '-', strtolower($language));
    }
}
