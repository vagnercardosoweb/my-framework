<?php

/**
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author    Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @copyright 31/05/2019 Vagner Cardoso
 */

namespace Core\Session {

    use Core\Helpers\Arr;

    /**
     * Class Session
     *
     * @package Core\Session
     * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
     */
    class Session
    {
        /**
         * @var array
         */
        protected $session = [];

        /**
         * Session constructor.
         */
        public function __construct()
        {
            if (!isset($_SESSION)) {
                $this->start();
            }

            $this->session = &$_SESSION;
        }

        /**
         * @inheritDoc
         */
        public function start()
        {
            if (!session_id()) {
                $current = session_get_cookie_params();

                session_set_cookie_params($current['lifetime'], $current['path'], $current['domain'], $current['secure'], true);
                session_name(md5(md5('VCWEBNETWORKS')));
                session_cache_limiter('nocache');

                session_start();
            }
        }

        /**
         * @return array
         */
        public function all()
        {
            return $this->session;
        }

        /**
         * @param string $key
         * @param mixed $value
         *
         * @return void
         */
        public function set($key, $value = null)
        {
            if (!is_array($key)) {
                $key = [$key => $value];
            }

            foreach ($key as $k => $v) {
                Arr::set($this->session, $k, $v);
            }
        }

        /**
         * @param string $key
         *
         * @return bool
         */
        public function has($key)
        {
            return $this->get($key, false);
        }

        /**
         * @param string $key
         * @param mixed $default
         *
         * @return mixed
         */
        public function get($key, $default = null)
        {
            return Arr::get($this->session, $key, $default);
        }

        /**
         * @param string $key
         *
         * @return mixed
         */
        public function forget($key)
        {
            $this->remove($key);
        }

        /**
         * @param string $key
         *
         * @return mixed
         */
        public function remove($key)
        {
            Arr::forget($this->session, $key);
        }

        /**
         * @inheritDoc
         */
        public function destroy()
        {
            $_SESSION = [];

            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();

                setcookie(
                    session_name(),
                    '',
                    (time() - 42000),
                    $params["path"],
                    $params["domain"],
                    $params["secure"],
                    $params["httponly"]
                );
            }

            if (session_status() == PHP_SESSION_ACTIVE) {
                session_destroy();

                $this->regenerate();
            }
        }

        /**
         * @inheritDoc
         */
        public function regenerate()
        {
            if (session_status() == PHP_SESSION_ACTIVE) {
                session_regenerate_id(true);
            }
        }
    }
}
