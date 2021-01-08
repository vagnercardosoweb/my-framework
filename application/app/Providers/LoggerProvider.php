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

use Core\Helpers\Path;
use Core\Logger;

/**
 * Class LoggerProvider.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class LoggerProvider extends Provider
{
    /**
     * @return string
     */
    public function name(): string
    {
        return 'logger';
    }

    /**
     * @return \Closure
     */
    public function register(): \Closure
    {
        return function () {
            return new Logger('app', Path::storage('/logs'));
        };
    }
}
