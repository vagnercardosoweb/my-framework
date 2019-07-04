<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 04/07/2019 Vagner Cardoso
 */

namespace Core\Session;

use Core\App;
use Core\Helpers\Arr;

/**
 * Class Flash.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class Flash
{
    /**
     * @var string
     */
    protected $key = '__flash__';

    /**
     * @var array|object
     */
    protected $data = [];

    /**
     * @var array
     */
    protected $storage;

    /**
     * Flash constructor.
     */
    public function __construct()
    {
        if (!isset($_SESSION)) {
            App::getInstance()->resolve('session')->start();
        }

        $this->storage = &$_SESSION[$this->key];

        if (!empty($this->storage) && is_array($this->storage)) {
            $this->data = $this->storage;
        }

        $this->storage = [];
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function set($name, $value)
    {
        // Cria um array vasio caso não exista a key
        if (empty($this->storage[$name])) {
            $this->storage[$name] = [];
        }

        // Adiciona uma nova mensagem
        $this->storage[$name] = $value;
    }

    /**
     * @return array
     */
    public function all()
    {
        return $this->data;
    }

    /**
     * @param string $key
     * @param string $default
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return Arr::get($this->data, $key, $default);
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function has($key)
    {
        return Arr::has($this->data, $key);
    }
}
