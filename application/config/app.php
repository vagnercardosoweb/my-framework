<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 13/02/2020 Vagner Cardoso
 */

return [
    // Slim 3+ Settings

    'slim' => [
        'httpVersion' => '1.1',
        'responseChunkSize' => 4096,
        'outputBuffering' => 'append',
        'determineRouteBeforeAppMiddleware' => true,
        'displayErrorDetails' => ('development' == env('APP_ENV', 'development')),
        'addContentLengthHeader' => true,
        'routerCacheFile' => false,
    ],

    // Sets the application versions

    'version' => [
        'framework' => 'v1.0.0',
        'skeleton' => 'v1.0.0',
    ],

    // Register providers

    'providers' => [
        \App\Providers\ViewProvider::class,
        \App\Providers\ErrorProvider::class,
        \App\Providers\ErrorSlackProvider::class,
        \App\Providers\SessionProvider::class,
        \App\Providers\DatabaseProvider::class,
        \App\Providers\MailerProvider::class,
        \App\Providers\EncryptionProvider::class,
        \App\Providers\PasswordProvider::class,
        \App\Providers\JwtProvider::class,
        \App\Providers\LoggerProvider::class,
        \App\Providers\EventProvider::class,
    ],

    // Register middleware

    'middleware' => [
        'automatic' => [
            \App\Middlewares\TrailingSlashMiddleware::class,
            \App\Middlewares\GenerateKeysMiddleware::class,
            \App\Middlewares\MaintenanceMiddleware::class,
            \App\Middlewares\OldParamMiddleware::class,
        ],

        'manual' => [],
    ],
];
