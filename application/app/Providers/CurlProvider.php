<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 05/07/2020 Vagner Cardoso
 */

namespace App\Providers;

use Core\Curl\Curl;

/**
 * Class CurlProvider.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class CurlProvider extends Provider
{
    /**
     * @return string
     */
    public function name(): string
    {
        return 'curl';
    }

    /**
     * @return \Closure
     */
    public function register(): \Closure
    {
        return function () {
            return new Curl();
        };
    }
}
