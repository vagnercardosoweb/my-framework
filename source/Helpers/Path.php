<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 20/05/2020 Vagner Cardoso
 */

namespace Core\Helpers;

/**
 * Class Path.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class Path
{
    /**
     * @param string $path
     *
     * @return string
     */
    public static function public_html(?string $path = null): string
    {
        return self::make('PUBLIC_FOLDER', 'public_html', self::root(), $path);
    }

    /**
     * @param string|null $path
     *
     * @return string
     */
    public static function root(?string $path = null): string
    {
        if (defined('ROOT')) {
            return constant('ROOT');
        }

        if (!isset($_SERVER['DOCUMENT_ROOT'])) {
            throw new \RuntimeException(
                'Constant [ROOT] not defined.'.
                'Or [DOCUMENT_ROOT] server not exists.'
            );
        }

        define('ROOT', realpath($_SERVER['DOCUMENT_ROOT']));

        return self::normalizePath(constant('ROOT'), $path);
    }

    /**
     * @param string $path
     *
     * @return string
     */
    public static function resource(?string $path = null): string
    {
        return self::make('RESOURCE_FOLDER', 'resources', self::app(), $path);
    }

    /**
     * @param string $path
     *
     * @return string
     */
    public static function storage(?string $path = null): string
    {
        return self::make('STORAGE_FOLDER', 'storage', self::app(), $path);
    }

    /**
     * @param string $path
     *
     * @return string
     */
    public static function app(?string $path = null): string
    {
        return self::make('APP_FOLDER', 'application', self::root(), $path);
    }

    /**
     * @param string      $name
     * @param string      $folder
     * @param string      $root
     * @param string|null $path
     *
     * @return string
     */
    protected static function make(
        string $name,
        string $folder,
        string $root,
        ?string $path = null
    ): string {
        if (!defined($name)) {
            define($name, self::normalizePath($root, $folder));
        }

        return self::normalizePath(constant($name), $path);
    }

    /**
     * @param string      $root
     * @param string|null $path
     *
     * @return string
     */
    protected static function normalizePath(string $root, ?string $path = null): string
    {
        return rtrim(sprintf('%s/%s', $root, trim($path, '\/')), '\/');
    }
}
