<?php

/**
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author    Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @copyright 31/05/2019 Vagner Cardoso
 */

return [

    /**
     * Configurações do slim
     */

    'slim' => [
        'httpVersion' => '1.1',
        'responseChunkSize' => 4096,
        'outputBuffering' => 'append',
        'determineRouteBeforeAppMiddleware' => true,
        'displayErrorDetails' => (env('APP_ENV', 'development') == 'development'),
        'addContentLengthHeader' => true,
        'routerCacheFile' => false,
    ],

    /**
     * Define a versão da aplicação
     */

    'version' => [

        /**
         * Versão do framework
         */

        'framework' => 'v1.0.0',

        /**
         * Versão do skeleton
         */

        'skeleton' => 'v1.0.0',

    ],

    /**
     * Registra os serviços
     */

    'providers' => [
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
        \App\Providers\ErrorSlackProvider::class,
    ],

    /**
     * Registra as middlewares
     */

    'middlewares' => [

        /**
         * Middlewares iniciada automática
         */

        'automatic' => [
            \App\Middlewares\GenerateKeysMiddleware::class,
            \App\Middlewares\TrailingSlashMiddleware::class,
            \App\Middlewares\MaintenanceMiddleware::class,
            \App\Middlewares\OldInputMiddleware::class,
        ],

        /**
         * Middlewares iniciada manual
         */

        'manual' => [],

    ],

];
