<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 25/01/2021 Vagner Cardoso
 */

namespace Core;

use Core\Helpers\Helper;
use Core\Helpers\Path;
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
     * @param string|string[]|null $name
     *
     * @return array<string|string|null>
     */
    public static function load($name = '.env'): ?array
    {
        $envPath = dirname(self::path());

        return Dotenv::create(self::repository(), $envPath, $name)->load();
    }

    /**
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        if (!$value = self::repository()->get($key)) {
            return $default;
        }

        $value = Helper::normalizeValueType($value);

        if (preg_match('/\A([\'"])(.*)\1\z/', $value, $matches)) {
            return $matches[2];
        }

        return is_string($value) ? trim($value) : $value;
    }

    /**
     * @return string
     */
    public static function path(): string
    {
        $env = Path::app('/.env');
        $example = Path::app('/.env.example');

        if (!file_exists($env) && file_exists($example)) {
            file_put_contents($env, file_get_contents($example));
        }

        return $env;
    }

    /**
     * @return array
     */
    protected static function adapters(): array
    {
        return [
            ServerConstAdapter::class,
            EnvConstAdapter::class,
            PutenvAdapter::class,
            ApacheAdapter::class,
        ];
    }

    /**
     * @return \Dotenv\Repository\RepositoryInterface
     */
    protected static function repository(): RepositoryInterface
    {
        if (null === self::$repository) {
            $repository = RepositoryBuilder::createWithNoAdapters();

            foreach (self::adapters() as $adapter) {
                $repository = $repository->addWriter($adapter);
                $repository = $repository->addAdapter($adapter);
            }

            self::$repository = $repository->immutable()->make();
        }

        return self::$repository;
    }
}
