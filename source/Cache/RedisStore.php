<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 25/02/2020 Vagner Cardoso
 */

namespace Core\Cache;

use Core\Interfaces\CacheStore;

/**
 * Class RedisStore.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class RedisStore implements CacheStore
{
    /**
     * @var \Core\Redis
     */
    protected $redis;

    /**
     * RedisStore constructor.
     *
     * @param \Core\Redis $client
     */
    public function __construct(\Core\Redis $client)
    {
        $this->redis = $client;
    }

    /**
     * @param string $key
     * @param mixed  $default
     * @param int    $seconds
     *
     * @return mixed
     */
    public function get(string $key, $default = null, int $seconds = null)
    {
        $value = $this->redis->get($key);

        if (empty($value)) {
            $value = $default instanceof \Closure ? $default() : $default;

            if (!is_null($seconds)) {
                $this->set($key, $value, $seconds);
            }
        }

        return is_string($value)
            ? $this->redis->unserialize($value)
            : $value;
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @param int    $seconds
     *
     * @return mixed
     */
    public function set(string $key, $value, int $seconds = null): bool
    {
        $value = $this->redis->serialize($value);

        if (-1 === $seconds) {
            return $this->redis->set($key, $value);
        }

        return (bool)$this->redis->setex(
            $key, $seconds ?? 60, $value
        );
    }

    /**
     * @return bool
     */
    public function flush(): bool
    {
        foreach ($this->redis->keys('*') as $key) {
            $this->delete(str_replace('cache:', '', $key));
        }

        return true;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function has(string $key): bool
    {
        return !is_null($this->redis->get($key));
    }

    /**
     * @param string $key
     * @param int    $value
     *
     * @return mixed
     */
    public function increment(string $key, $value = 1): bool
    {
        return (bool)$this->redis->incrby($key, $value);
    }

    /**
     * @param string $key
     * @param int    $value
     *
     * @return mixed
     */
    public function decrement(string $key, $value = 1): bool
    {
        return (bool)$this->redis->decrby($key, $value);
    }

    /**
     * @param string|array $key
     *
     * @return bool
     */
    public function delete($key): bool
    {
        return (bool)$this->redis->del($key);
    }
}
