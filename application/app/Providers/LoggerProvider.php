<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 03/08/2019 Vagner Cardoso
 */

namespace App\Providers;

use Core\Logger;

/**
 * Class LoggerProvider.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class LoggerProvider extends Provider
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->container['logger'] = function () {
            return new Logger(
                'VCWebNetworks', APP_FOLDER.'/storage/logs'
            );
        };
    }
}
