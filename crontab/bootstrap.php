<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 08/06/2021 Vagner Cardoso
 */

use Core\App;

try {
    // Constants
    define('ROOT', realpath(dirname(__DIR__)));
    define('PUBLIC_FOLDER', __DIR__.'/../public_html');
    define('APP_FOLDER', __DIR__.'/../application');
    define('RESOURCE_FOLDER', APP_FOLDER.'/resources');
    define('BASE_URL', 'http://localhost');

    // Autoload
    require_once APP_FOLDER.'/vendor/autoload.php';

    // Loader app
    $app = App::getInstance();
    $app->registerProviders();
    $app->registerEvents();
} catch (\Exception $e) {
    exit("ERROR: {$e->getMessage()}\n");
}
