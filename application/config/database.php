<?php

/**
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>.
 *
 * @author    Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @copyright 31/05/2019 Vagner Cardoso
 */

return [
    /*
     * Eventos
     *
     * tbName:creating | tbName:created
     * tbName:updating | tbName:updated
     * tbName:deleting | tbName:deleted
     */

    /*
     * Default
     *
     * Driver de conexão padrão
     */

    'default' => env('DB_DRIVER', 'mysql'),

    /*
     * Options
     *
     * Configura as opções para conexão
     */

    'options' => [
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ,
        \PDO::ATTR_CASE => \PDO::CASE_NATURAL,
        \PDO::ATTR_PERSISTENT => false,
    ],

    /*
     * Drivers
     *
     * Define os tipo de conexões que serão aceitos
     */

    'connections' => [
        /*
         * MySQL
         */

        'mysql' => [
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', 3306),
            'username' => env('DB_USER', ''),
            'password' => env('DB_PASS', ''),
            'database' => env('DB_DATABASE', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATE', 'utf8mb4_general_ci'),
            'timezone' => env('APP_TIMEZONE', null),
        ],

        /*
         * PostgreSQL
         */

        'pgsql' => [
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', 5432),
            'username' => env('DB_USER', ''),
            'password' => env('DB_PASS', ''),
            'database' => env('DB_DATABASE', ''),
            'schema' => ['public'],
            'charset' => env('DB_CHARSET', 'utf8'),
            'timezone' => env('APP_TIMEZONE', null),
        ],

        /*
         * SQLServer
         *
         * conexão padrão usa o driver "pdo_dblib" e caso
         * queira usar o driver "sqlsrv" você deve passar o "dsn"
         * manual conforme a linha "89" comentada
         */

        'dblib' => [
            // 'dsn' => 'sqlsrv:Server=%s;Connect=%s;ConnectionPooling=0',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', 1433),
            'username' => env('DB_USER', ''),
            'password' => env('DB_PASS', ''),
            'database' => env('DB_DATABASE', ''),
            'charset' => env('DB_CHARSET', 'utf8'),
        ],
    ],
];
