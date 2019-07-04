<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 04/07/2019 Vagner Cardoso
 */

namespace Core;

use Dotenv\Dotenv;
use Dotenv\Environment\Adapter\EnvConstAdapter;
use Dotenv\Environment\Adapter\PutenvAdapter;
use Dotenv\Environment\Adapter\ServerConstAdapter;
use Dotenv\Environment\DotenvFactory;

/**
 * Class Loader.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class Loader
{
    /**
     * {@inheritdoc}
     */
    public static function dotEnvironment()
    {
        $pathEnv = APP_FOLDER.'/.env';

        if (file_exists($pathEnv)) {
            Dotenv::create(APP_FOLDER, '.env', new DotenvFactory([
                new EnvConstAdapter(),
                new PutenvAdapter(),
                new ServerConstAdapter(),
            ]))->overload();
        } else {
            $pathEnvExample = APP_FOLDER.'/.env-example';

            if (!file_exists($pathEnv) && (file_exists($pathEnvExample) && !is_dir($pathEnvExample))) {
                file_put_contents($pathEnv, file_get_contents($pathEnvExample), FILE_APPEND);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function defaultPhpConfig()
    {
        // Configurações básicas

        $charset = env('APP_CHARSET', 'UTF-8');
        $locale = env('APP_LOCALE', 'pt_BR');

        ini_set('default_charset', $charset);
        mb_internal_encoding($charset);
        date_default_timezone_set(env('APP_TIMEZONE', 'America/Sao_Paulo'));
        setlocale(LC_ALL, $locale, "{$locale}.{$charset}");

        // Configurações de erro

        ini_set('log_errors', ('true' == env('INI_LOG_ERRORS', 'true')));
        ini_set('error_log', sprintf(env('INI_ERROR_LOG', APP_FOLDER.'/storage/logs/php-%s.log'), date('dmY')));
        ini_set('display_errors', env('INI_DISPLAY_ERRORS', 'On'));
        ini_set('display_startup_errors', env('INI_DISPLAY_STARTUP_ERRORS', 'On'));

        if ('development' == env('APP_ENV', 'development')) {
            error_reporting(E_ALL ^ E_DEPRECATED);
        } else {
            error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED);
        }

        if ('true' == env('APP_ERROR_HANDLER', 'true')) {
            set_error_handler(function ($level, $message, $file = '', $line = 0) {
                if (error_reporting() & $level) {
                    throw new \ErrorException(
                        $message, 0, $level, $file, $line
                    );
                }
            });
        }
    }

    /**
     * @param \Core\App $app
     * @param array     $providers
     */
    public static function providers(App $app, array $providers = [])
    {
        $providers = $providers ?: config('app.providers', []);

        foreach ($providers as $provider) {
            if (class_exists($provider)) {
                $provider = new $provider($app);

                if (method_exists($provider, 'register')) {
                    $provider->register();
                }

                if (method_exists($provider, 'boot')) {
                    $provider->boot();
                }
            }
        }
    }

    /**
     * @param \Core\App $app
     * @param array     $middlewares
     */
    public static function middlewares(App $app, array $middlewares = [])
    {
        $middlewares = $middlewares ?: config('app.middlewares.automatic', []);

        foreach ($middlewares as $name => $middleware) {
            if (class_exists($middleware)) {
                $app->add(new $middleware($app->getContainer()));
            }
        }
    }

    /**
     * @param \Core\App   $app
     * @param string|null $folder
     */
    public static function routes(App $app, ?string $folder = '')
    {
        $io = function ($file, $app) {
            include_once "{$file}";
        };

        $file = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $folder ?: APP_FOLDER.'/routes', \FilesystemIterator::SKIP_DOTS
            )
        );

        $file->rewind();

        while ($file->valid()) {
            if ($file->isFile()) {
                $io($file->getRealPath(), $app);
            }

            $file->next();
        }
    }
}
