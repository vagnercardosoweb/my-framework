<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 07/06/2021 Vagner Cardoso
 */

use Core\App;
use Core\Env;
use Core\Helpers\Path;
use Core\Phinx\Migration;

try {
    // Constants
    define('ROOT', __DIR__);
    define('PUBLIC_FOLDER', __DIR__.'/../public_html');
    define('APP_FOLDER', __DIR__);
    define('RESOURCE_FOLDER', __DIR__.'/resources');
    define('BASE_URL', 'http://localhost');

    // Autoload
    require_once __DIR__.'/vendor/autoload.php';

    // Loader app
    $app = App::getInstance();
    $app->registerProviders();
    $app->registerEvents();

    // Configuration:
    //
    // @see https://book.cakephp.org/3.0/en/phinx/configuration.html

    return [
        'version_order' => 'creation',
        'migration_base_class' => Migration::class,
        'templates' => ['file' => Path::storage('/database/templates/Migration.php.dist')],

        'paths' => [
            'migrations' => Path::storage('/database/migrations'),
            'seeds' => Path::storage('/database/seeds'),
        ],

        'environments' => [
            'default_migration_table' => 'migrations',
            'default_database' => Env::get('DB_DRIVER', 'mysql'),

            'mysql' => [
                'adapter' => 'mysql',
                'host' => Env::get('DB_HOST', 'localhost'),
                'port' => Env::get('DB_PORT', '3306'),
                'name' => Env::get('DB_DATABASE', null),
                'user' => Env::get('DB_USER', null),
                'pass' => Env::get('DB_PASS', null),
                'charset' => Env::get('DB_CHARSET', 'utf8'),
                'collation' => Env::get('DB_COLLATE', 'utf8_general_ci'),
                'table_prefix' => false,
                'table_suffix' => false,
            ],

            'pgsql' => [
                'adapter' => 'pgsql',
                'host' => Env::get('DB_HOST', 'localhost'),
                'port' => Env::get('DB_PORT', '3306'),
                'name' => Env::get('DB_DATABASE', null),
                'user' => Env::get('DB_USER', null),
                'pass' => Env::get('DB_PASS', null),
                'charset' => Env::get('DB_CHARSET', 'utf8'),
                'collation' => false,
                'table_prefix' => false,
                'table_suffix' => false,
            ],

            'sqlsrv' => [
                'adapter' => 'sqlsrv',
                'host' => Env::get('DB_HOST', '127.0.0.1'),
                'port' => Env::get('DB_PORT', '1433'),
                'name' => Env::get('DB_DATABASE', null),
                'user' => Env::get('DB_USER', null),
                'pass' => Env::get('DB_PASS', null),
                'charset' => 65001, // \PDO::SQLSRV_ENCODING_UTF8
                'collation' => false,
                'table_prefix' => false,
                'table_suffix' => false,
            ],
        ],
    ];
} catch (\Exception $e) {
    exit("ERROR: {$e->getMessage()}\n");
}
