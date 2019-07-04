<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 04/07/2019 Vagner Cardoso
 */

namespace App\Providers;

use Core\Database\Connect;

/**
 * Class DatabaseProvider.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class DatabaseProvider extends Provider
{
    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function register(): void
    {
        // Connect instance
        $connect = new Connect();
        $connect->setDefaultConnection(config('database.default', 'mysql'));

        // Add connections config
        foreach (config('database.connections') as $driver => $config) {
            $connect->addConnection($config, $driver);
        }

        // Add connect provider
        $this->container['connect'] = function () use ($connect) {
            return $connect;
        };

        // Add connect default database provider (mysql)
        $this->container['db'] = function () use ($connect) {
            return $connect->connection();
        };
    }
}
