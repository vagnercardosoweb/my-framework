<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 07/06/2021 Vagner Cardoso
 */

use Core\Env;
use Core\Helpers\Path;

return [
    // Slim 3+ Settings

    'slim' => [
        'httpVersion' => '1.1',
        'responseChunkSize' => 4096,
        'outputBuffering' => 'append',
        'determineRouteBeforeAppMiddleware' => true,
        'displayErrorDetails' => ('development' === Env::get('APP_ENV', 'development')),
        'addContentLengthHeader' => true,
        'routerCacheFile' => false,
    ],

    // Sets the application versions

    'version' => [
        'framework' => 'v1.0.0',
        'skeleton' => 'v1.0.0',
    ],

    // Register path routes

    'routes' => [
        'app' => [
            Path::app('/routes/api.php'),
            Path::app('/routes/web.php'),
        ],

        'console' => [
            Path::app('/routes/console.php'),
        ],
    ],

    // Register providers

    'providers' => [
        \App\Providers\ConfigProvider::class,
        \App\Providers\ViewProvider::class,
        \App\Providers\CurlProvider::class,
        \App\Providers\EventProvider::class,
        \App\Providers\RedisProvider::class,
        \App\Providers\CacheProvider::class,
        \App\Providers\LoggerProvider::class,
        \App\Providers\JwtProvider::class,
        \App\Providers\EncryptionProvider::class,
        \App\Providers\PasswordProvider::class,
        \App\Providers\SessionProvider::class,
        \App\Providers\FlashProvider::class,
        \App\Providers\DatabaseProvider::class,
        \App\Providers\MailerProvider::class,
        \App\Providers\ErrorProvider::class,
        \App\Providers\PhpErrorProvider::class,
        \App\Providers\NotFoundProvider::class,
        \App\Providers\NotAllowedProvider::class,
        \App\Providers\EnvironmentProvider::class,
        \App\Providers\TranslatorProvider::class,
    ],

    // Register middleware

    'middleware' => [
        'app' => [
            \App\Middlewares\TrailingSlashMiddleware::class,
            \App\Middlewares\GenerateKeysMiddleware::class,
            \App\Middlewares\MaintenanceMiddleware::class,
            \App\Middlewares\OldParamMiddleware::class,
            // \App\Middlewares\CorsMiddleware::class
        ],

        'manual' => [],
    ],

    // Register events

    'events' => [
        \App\Events\ErrorSlackEvent::class,
        \App\Events\ExampleEvent::class,
    ],
];
