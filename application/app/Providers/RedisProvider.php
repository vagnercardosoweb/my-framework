<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 20/05/2020 Vagner Cardoso
 */

namespace App\Providers;

use Core\Config;
use Core\Redis;

/**
 * Class RedisProvider.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class RedisProvider extends Provider
{
    /**
     * @return string|array
     */
    public function name()
    {
        return 'redis';
    }

    /**
     * @return \Closure
     */
    public function register(): \Closure
    {
        return function () {
            return new Redis(Config::get('redis'));
        };
    }
}
