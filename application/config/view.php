<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 04/07/2019 Vagner Cardoso
 */

use Core\App;

return [
    // Configura as opções padões do twig

    'options' => [
        'debug' => true,
        'charset' => 'UTF-8',
        'strict_variables' => false,
        'autoescape' => 'html',
        'cache' => ('production' == env('APP_ENV') ? APP_FOLDER.'/storage/cache' : false),
        'auto_reload' => true,
        'optimizations' => -1,
    ],

    /*
     * Templates
     *
     * Define os templates no carregamento das views
     *
     * OBS: Não altere os nomes dos indice e caso altere o caminho do template
     *      deverá verificar e renomear o nome da pasta.
     */

    'templates' => [
        'web' => RESOURCE_FOLDER.'/views/web',
        'error' => RESOURCE_FOLDER.'/views/error',
        'mail' => RESOURCE_FOLDER.'/views/mail',
    ],

    // Registra funções e filtros para usar na view

    'registers' => [
        'functions' => [
            'asset' => 'asset',
            'asset_source' => 'asset_source',
            'config' => 'config',
            'path_for' => '\Core\Router::pathFor',
            'is_current_router' => '\Core\Router::isCurrent',
            'has_current_router' => '\Core\Router::hasCurrent',
            'has_container' => function ($name, $params = []) {
                return App::getInstance()->resolve($name, $params);
            },
            'csrf_token' => function ($input = true) {
                $token = App::getInstance()->resolve('encryption')->encrypt([
                    'token' => uniqid(rand(), true),
                    'expired' => time() + (60 * 60 * 24),
                ]);

                return $input
                    ? "<input type='hidden' name='_csrfToken' id='_csrfToken' value='{$token}'/>"
                    : $token;
            },
            'placeholder' => 'placeholder',
            'error_code_type' => 'error_code_type',
        ],

        'filters' => [
            'is_string' => 'is_string',
            'is_array' => 'is_array',
            'get_day' => 'get_day_string',
            'get_month' => 'get_month_string',
        ],
    ],
];
