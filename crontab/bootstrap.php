<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 04/07/2019 Vagner Cardoso
 */

use Core\App;
use Core\Loader;

try {
    // Constants
    define('ROOT', __DIR__);
    define('PUBLIC_FOLDER', __DIR__.'/../public_html');
    define('APP_FOLDER', __DIR__.'/../application');
    define('RESOURCE_FOLDER', APP_FOLDER.'/resources');
    define('BASE_URL', 'http://localhost');

    // Autoload
    require_once APP_FOLDER.'/vendor/autoload.php';

    // Loader app
    $app = App::getInstance();

    // Loader providers
    Loader::providers($app, [
        \App\Providers\ViewProvider::class,
        \App\Providers\ErrorProvider::class,
        \App\Providers\SessionProvider::class,
        \App\Providers\DatabaseProvider::class,
        \App\Providers\MailerProvider::class,
        \App\Providers\EncryptionProvider::class,
        \App\Providers\PasswordProvider::class,
        \App\Providers\JwtProvider::class,
        \App\Providers\LoggerProvider::class,
        \App\Providers\EventProvider::class,
    ]);
} catch (\Exception $e) {
    die("ERROR: {$e->getMessage()}\n");
}
