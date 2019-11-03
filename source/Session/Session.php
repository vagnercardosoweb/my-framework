<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 03/11/2019 Vagner Cardoso
 */

namespace Core\Session;

use Core\Helpers\Obj;

/**
 * Class Session.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class Session
{
    /**
     * @var string
     */
    protected $key = 'vcw:session';

    /**
     * @var object
     */
    protected $storage;

    /**
     * Session constructor.
     *
     * @throws \Exception
     */
    public function __construct()
    {
        if ('true' != env('APP_SESSION', true)) {
            throw new \Exception('Session is not enabled.');
        }

        $this->start();

        $this->storage = &$_SESSION[$this->key];
        $this->storage = Obj::fromArray($this->storage);
    }

    /**
     * @param string $name
     *
     * @return mixed|null
     */
    public function __get(string $name)
    {
        return $this->get($name);
    }

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return void
     */
    public function __set(string $name, $value): void
    {
        $this->set($name, $value);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return $this->has($name);
    }

    /**
     * @param string $name
     *
     * @return void
     */
    public function __unset(string $name): void
    {
        $this->remove($name);
    }

    /**
     * @return void
     */
    public function start(): void
    {
        if (!session_id() && PHP_SESSION_NONE === session_status()) {
            $current = $this->cookieParams();
            $cacheLimiter = env('APP_SESSION_CACHE_LIMITER', null);
            $sessionSave = env('APP_SESSION_SAVE_PATH', false);
            $sessionName = isset($_SERVER) ? $_SERVER['HTTP_HOST'] : 'vcw:session_name';

            session_set_cookie_params(
                $current['lifetime'],
                $current['path'],
                $current['domain'],
                $current['secure'],
                true
            );

            session_name(md5(sha1($sessionName)));

            if (null !== $cacheLimiter) {
                session_cache_limiter($cacheLimiter);
            }

            if ('true' == $sessionSave) {
                session_save_path(APP_FOLDER.'/storage/sessions');
            }

            session_start();
        }
    }

    /**
     * @return array
     */
    public function cookieParams(): array
    {
        return session_get_cookie_params();
    }

    /**
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get(string $name, $default = null)
    {
        if (isset($this->storage->{$name})) {
            return $this->storage->{$name};
        }

        return $default;
    }

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return void
     */
    public function set(string $name, $value): void
    {
        if (is_array($value)) {
            $value = Obj::fromArray($value);
        }

        $this->storage->{$name} = $value;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->storage->{$name});
    }

    /**
     * @param string $name
     *
     * @return void
     */
    public function remove(string $name): void
    {
        if ($this->has($name)) {
            unset($this->storage->{$name});
        }
    }

    /**
     * @return string
     */
    public function id(): string
    {
        return session_id();
    }

    /**
     * @return object
     */
    public function all()
    {
        return $this->storage;
    }

    /**
     * @return void
     */
    public function clear(): void
    {
        $this->storage = new \stdClass();

        if (ini_get('session.use_cookies')) {
            $params = $this->cookieParams();

            setcookie(
                $this->name(),
                '',
                (time() - 42000),
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        if ($this->active()) {
            session_destroy();
            session_unset();

            $this->regenerate();
        }

        session_write_close();
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return session_name();
    }

    /**
     * @return bool
     */
    public function active(): bool
    {
        return PHP_SESSION_ACTIVE === session_status();
    }

    /**
     * @return void
     */
    public function regenerate(): void
    {
        if ($this->active()) {
            session_regenerate_id(true);
        }
    }
}
