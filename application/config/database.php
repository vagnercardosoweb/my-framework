<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 04/07/2019 Vagner Cardoso
 */

return [
    /*
     * Events
     *
     * tbName:creating | tbName:created
     * tbName:updating | tbName:updated
     * tbName:deleting | tbName:deleted
     */

    // Default connection driver
    'default' => env('DB_DRIVER', 'mysql'),

    // Defines the types of connections that will be accepted
    'connections' => [
        'mysql' => [
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', 3306),
            'username' => env('DB_USER', null),
            'password' => env('DB_PASS', null),
            'database' => env('DB_DATABASE', null),
            'charset' => env('DB_CHARSET', 'utf8'),
            'collation' => env('DB_COLLATE', 'utf8_general_ci'),
            'timezone' => env('APP_TIMEZONE', null),
            'options' => [], // Use pdo connection options \PDO::ATTR... => \PDO::...
            'attributes' => [], // Use pdo->setAttribute(key => value)
            'commands' => [], // Use pdo->exec(...command...)
        ],

        'pgsql' => [
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', 5432),
            'username' => env('DB_USER', null),
            'password' => env('DB_PASS', null),
            'database' => env('DB_DATABASE', null),
            'schema' => ['public'],
            'charset' => env('DB_CHARSET', 'utf8'),
            'timezone' => env('APP_TIMEZONE', null),
            'options' => [], // Use pdo connection options \PDO::ATTR... => \PDO::...
            'attributes' => [], // Use pdo->setAttribute(key => value)
            'commands' => [], // Use pdo->exec(...command...)
        ],

        'sqlsrv' => [
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', 1433),
            'username' => env('DB_USER', null),
            'password' => env('DB_PASS', null),
            'database' => env('DB_DATABASE', null),
            'charset' => env('DB_CHARSET', 'utf8'),
            'options' => [], // Use pdo connection options \PDO::ATTR... => \PDO::...
            'attributes' => [], // Use pdo->setAttribute(key => value)
            'commands' => [], // Use pdo->exec(...command...)
        ],
    ],
];
