<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 08/01/2021 Vagner Cardoso
 */

use Core\Env;

return [
    /*
     * Model events
     *
     * table:creating | table:created
     * table:updating | table:updated
     * table:deleting | table:deleted
     */

    // Default connection driver

    'default' => Env::get('DB_DRIVER', 'mysql'),

    // Defines the types of connections that will be accepted

    'connections' => [
        'sqlite' => [
            'url' => null,
            'driver' => 'sqlite',
            'database' => 'PATH_DATABASE_SQLITE', // Location database sqlite
            'options' => [], // Use pdo connection options \PDO::ATTR... => \PDO::...
            'attributes' => [], // Use pdo->setAttribute(key => value)
            'commands' => [], // Use pdo->exec(...command...)
            'events' => [], // Register Class::class instance of ConnectionEvent
        ],

        'mysql' => [
            'url' => null,
            'driver' => 'mysql',
            'host' => Env::get('DB_HOST', 'localhost'),
            'port' => Env::get('DB_PORT', 3306),
            'username' => Env::get('DB_USER', null),
            'password' => Env::get('DB_PASS', null),
            'notPassword' => false,
            'database' => Env::get('DB_DATABASE', null),
            'charset' => Env::get('DB_CHARSET', 'utf8mb4'),
            'collation' => Env::get('DB_COLLATE', 'utf8mb4_unicode_ci'),
            'timezone' => Env::get('DB_TIMEZONE', '+3:00'),
            'options' => [], // Use pdo connection options \PDO::ATTR... => \PDO::...
            'attributes' => [], // Use pdo->setAttribute(key => value)
            'commands' => [], // Use pdo->exec(...command...)
            'events' => [], // Register Class::class instance of ConnectionEvent
        ],

        'pgsql' => [
            'url' => null,
            'driver' => 'pgsql',
            'host' => Env::get('DB_HOST', 'localhost'),
            'port' => Env::get('DB_PORT', 5432),
            'username' => Env::get('DB_USER', null),
            'password' => Env::get('DB_PASS', null),
            'database' => Env::get('DB_DATABASE', null),
            'schema' => ['public'],
            'charset' => Env::get('DB_CHARSET', 'utf8'),
            'timezone' => Env::get('DB_TIMEZONE', '+3:00'),
            'options' => [], // Use pdo connection options \PDO::ATTR... => \PDO::...
            'attributes' => [], // Use pdo->setAttribute(key => value)
            'commands' => [], // Use pdo->exec(...command...)
            'events' => [], // Register Class::class instance of ConnectionEvent
        ],

        'sqlsrv' => [
            'url' => null,
            'driver' => 'sqlsrv',
            'host' => Env::get('DB_HOST', '127.0.0.1'),
            'port' => Env::get('DB_PORT', 1433),
            'username' => Env::get('DB_USER', null),
            'password' => Env::get('DB_PASS', null),
            'database' => Env::get('DB_DATABASE', null),
            'charset' => Env::get('DB_CHARSET', 'utf8'),
            'options' => [], // Use pdo connection options \PDO::ATTR... => \PDO::...
            'attributes' => [], // Use pdo->setAttribute(key => value)
            'commands' => [], // Use pdo->exec(...command...)
            'events' => [], // Register Class::class instance of ConnectionEvent
        ],
    ],
];
