<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 25/01/2021 Vagner Cardoso
 */

namespace App\Providers;

use Core\Config;

/**
 * Class ConfigProvider.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class ConfigProvider extends Provider
{
    /**
     * @return string|array
     */
    public function name()
    {
        return 'config';
    }

    /**
     * @return \Closure
     */
    public function register(): \Closure
    {
        return function () {
            return new Config();
        };
    }
}
