<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 08/01/2021 Vagner Cardoso
 */

namespace App\Providers;

use Core\Cache\Cache;
use Core\Config;

/**
 * Class CacheProvider.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class CacheProvider extends Provider
{
    /**
     * @return string|array
     */
    public function name()
    {
        return 'cache';
    }

    /**
     * @return \Closure
     */
    public function register(): \Closure
    {
        return function () {
            return new Cache(Config::get('cache'));
        };
    }
}
