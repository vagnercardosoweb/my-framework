<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author    Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @copyright 31/05/2019 Vagner Cardoso
 */

namespace App\Providers {
    use Core\Database\Database;

    /**
     * Class DatabaseProvider.
     *
     * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
     */
    class DatabaseProvider extends Provider
    {
        public function register()
        {
            /*
             * @return Database
             */
            $this->container['db'] = function () {
                return Database::getInstance();
            };
        }
    }
}
