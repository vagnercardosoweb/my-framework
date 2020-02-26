<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 26/02/2020 Vagner Cardoso
 */

namespace Core;

use Core\Helpers\Helper;
use Dotenv\Dotenv;
use Dotenv\Repository\Adapter\ApacheAdapter;
use Dotenv\Repository\Adapter\EnvConstAdapter;
use Dotenv\Repository\Adapter\PutenvAdapter;
use Dotenv\Repository\Adapter\ServerConstAdapter;
use Dotenv\Repository\RepositoryBuilder;
use Dotenv\Repository\RepositoryInterface;

/**
 * Class Env.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class Env
{
    /**
     * @var \Dotenv\Repository\RepositoryInterface
     */
    protected static $repository;

    /**
     * @param string|string[]|null $names
     *
     * @return array
     */
    public static function load($names = '.env'): array
    {
        return Dotenv::create(
            self::repository(),
            self::path(),
            $names
        )->load();
    }

    /**
     * @return array
     */
    public static function adapters(): array
    {
        return [
            new ApacheAdapter(),
            new EnvConstAdapter(),
            new ServerConstAdapter(),
            new PutenvAdapter(),
        ];
    }

    /**
     * @return \Dotenv\Repository\RepositoryInterface
     */
    public static function repository(): RepositoryInterface
    {
        if (null === static::$repository) {
            static::$repository = RepositoryBuilder::create()
                ->withReaders(self::adapters())
                ->withWriters(self::adapters())
                ->immutable()
                ->make()
            ;
        }

        return static::$repository;
    }

    /**
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        if (!$value = static::repository()->get($key)) {
            return $default;
        }

        $value = Helper::normalizeValueType($value);

        if (preg_match('/\A([\'"])(.*)\1\z/', $value, $matches)) {
            return $matches[2];
        }

        return trim($value);
    }

    /**
     * @return string
     */
    private static function path(): string
    {
        $env = \Core\Helpers\Path::app('/.env');
        $example = \Core\Helpers\Path::app('/.env.example');

        if (!file_exists($env) && file_exists($example)) {
            file_put_contents($env, file_get_contents($example));
        }

        return str_replace('.env', '/', $env);
    }
}
