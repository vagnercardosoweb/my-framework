<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 04/07/2019 Vagner Cardoso
 */

return [
    // Configurações do slim

    'slim' => [
        'httpVersion' => '1.1',
        'responseChunkSize' => 4096,
        'outputBuffering' => 'append',
        'determineRouteBeforeAppMiddleware' => true,
        'displayErrorDetails' => ('development' == env('APP_ENV', 'development')),
        'addContentLengthHeader' => true,
        'routerCacheFile' => false,
    ],

    // Define a versão da aplicação

    'version' => [
        'framework' => 'v1.0.0',
        'skeleton' => 'v1.0.0',
    ],

    // Registra os serviços

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

    // Registra as middlewares

    'middlewares' => [
        // Middlewares iniciada automática

        'automatic' => [
            \App\Middlewares\TrailingSlashMiddleware::class,
            \App\Middlewares\GenerateKeysMiddleware::class,
            \App\Middlewares\MaintenanceMiddleware::class,
            \App\Middlewares\OldParamMiddleware::class,
        ],

        // Middlewares iniciada manual

        'manual' => [],
    ],
];
