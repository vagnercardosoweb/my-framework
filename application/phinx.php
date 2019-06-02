<?php

/**
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author    Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @copyright 31/05/2019 Vagner Cardoso
 */

use Core\App;
use Core\Loader;

try {
    /**
     * Constantes
     */

    define('ROOT', __DIR__);
    define('PUBLIC_FOLDER', __DIR__.'/../public_html');
    define('APP_FOLDER', __DIR__);
    define('RESOURCE_FOLDER', __DIR__.'/resources');
    define('BASE_URL', "http://localhost");

    /**
     * Autoload
     */

    require_once APP_FOLDER.'/vendor/autoload.php';

    /**
     * Carrega a aplicação
     */

    $app = App::getInstance();

    /**
     * Carrega os serviços
     */

    Loader::providers($app, [
        \App\Providers\DatabaseProvider::class,
        \App\Providers\EncryptionProvider::class,
        \App\Providers\PasswordProvider::class,
        \App\Providers\JwtProvider::class,
    ]);

    /**
     * Configurações do phinx
     */

    return [
        /**
         * Class padrão para a migração
         */

        'migration_base_class' => \Core\Contracts\Migration::class,

        /**
         * Template para criação da migration
         */

        'templates' => [
            'file' => __DIR__.'/storage/database/templates/Migration.php.dist',
        ],

        /**
         * Caminhos relativos até a pasta que será salvo os arquivos
         * de migrations, seeds e bootstrapper
         */

        'paths' => [
            'migrations' => __DIR__.'/storage/database/migrations',
            'seeds' => __DIR__.'/storage/database/seeds',
        ],

        /**
         * Configurações que é usada no escopo do phinx
         */

        'environments' => [
            'default_migration_table' => 'migrations',
            'default_database' => env('DB_DRIVER', 'mysql'),

            /**
             * MySQL
             */

            'mysql' => [
                'adapter' => 'mysql',
                'host' => env('DB_HOST', 'localhost'),
                'port' => env('DB_PORT', '3306'),
                'name' => env('DB_DATABASE', ''),
                'user' => env('DB_USER', ''),
                'pass' => env('DB_PASS', ''),
                'charset' => env('DB_CHARSET', 'utf8'),
                'collation' => env('DB_COLLATE', 'utf8_general_ci'),
                'table_prefix' => false,
                'table_suffix' => false,
            ],

            /**
             * PostgreSQL
             */

            'pgsql' => [
                'adapter' => 'pgsql',
                'host' => env('DB_HOST', 'localhost'),
                'port' => env('DB_PORT', '3306'),
                'name' => env('DB_DATABASE', ''),
                'user' => env('DB_USER', ''),
                'pass' => env('DB_PASS', ''),
                'charset' => env('DB_CHARSET', 'utf8'),
                'collation' => false,
                'table_prefix' => false,
                'table_suffix' => false,
            ],

            /**
             * SQLServer
             */

            'sqlsrv' => [
                'adapter' => 'sqlsrv',
                'host' => env('DB_HOST', '127.0.0.1'),
                'port' => env('DB_PORT', '1433'),
                'name' => env('DB_DATABASE', ''),
                'user' => env('DB_USER', ''),
                'pass' => env('DB_PASS', ''),
                'charset' => 65001, // \PDO::SQLSRV_ENCODING_UTF8
                'collation' => false,
                'table_prefix' => false,
                'table_suffix' => false,
            ],
        ],

        /**
         * Ordem da versionamento
         *
         * creation:
         * - As migrações são ordenadas pelo tempo de criação, que também faz parte do nome do arquivo.
         *
         * execution:
         * - As migrações são ordenadas pelo tempo de execução, também conhecido como horário de início.
         */

        'version_order' => 'creation',
    ];
} catch (\Exception $e) {
    die("ERROR: {$e->getMessage()}\n");
}
