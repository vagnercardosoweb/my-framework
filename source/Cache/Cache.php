<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 26/02/2020 Vagner Cardoso
 */

namespace Core\Cache;

use Core\Interfaces\CacheStore;
use Core\Redis;

/**
 * Class Cache.
 *
 * @method mixed get(string $key, $default = null, int $seconds = null)
 * @method bool set(string $key, $value, int $seconds = 1)
 * @method bool flush()
 * @method bool has(string $key)
 * @method bool increment(string $key, $value = 1)
 * @method bool decrement(string $key, $value = 1)
 * @method bool delete($key)
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class Cache
{
    /**
     * @var array|string
     */
    protected $config;

    /**
     * @var string
     */
    protected $driver;

    /**
     * @var array
     */
    protected $stores = [];

    /**
     * Cache constructor.
     *
     * @param array       $config Configuration redis ou directory file
     * @param string|null $driver
     */
    public function __construct($config = null, string $driver = null)
    {
        $this->config = $config;
        $this->setDriver($driver ?? 'redis');
    }

    /**
     * @param string $method
     * @param mixed  $arguments
     *
     * @return mixed
     */
    public function __call(string $method, $arguments)
    {
        return $this->store()->{$method}(...$arguments);
    }

    /**
     * @param string|null $driver
     *
     * @return \Core\Interfaces\CacheStore
     */
    public function store(string $driver = null): CacheStore
    {
        $driver = $driver ?? $this->getDriver();
        $method = sprintf('create%sDriver', ucfirst($driver));

        if (!method_exists($this, $method)) {
            throw new \InvalidArgumentException("Driver [{$driver}] not supported in cache.");
        }

        if ($this->stores[$driver] instanceof CacheStore) {
            return $this->stores[$driver];
        }

        $this->stores[$driver] = $this->{$method}();

        return $this->stores[$driver];
    }

    /**
     * @return string
     */
    public function getDriver(): string
    {
        return $this->driver;
    }

    /**
     * @param string $driver
     *
     * @return \Core\Cache\Cache
     */
    public function setDriver(string $driver): Cache
    {
        if (empty($this->stores[$driver])) {
            $this->stores[$driver] = null;
        }

        $this->driver = $driver;

        return $this;
    }

    /**
     * @return \Core\Interfaces\CacheStore
     */
    public function createRedisDriver(): CacheStore
    {
        return new RedisStore(
            new Redis($this->config, [
                'prefix' => 'cache:',
            ])
        );
    }
}
