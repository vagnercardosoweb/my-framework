<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 13/02/2020 Vagner Cardoso
 */

$app->group(['prefix' => '/api', 'namespace' => 'Api/'], function () use ($app) {
    // Deploy
    $app->group(['prefix' => '/deploy', 'namespace' => 'Deploy/'], function () use ($app) {
        $app->route('post', '/gitlab', 'GitlabController');
        $app->route('post', '/github', 'GithubController');
        $app->route('post', '/bitbucket', 'BitbucketController');
    })->add(\App\Middlewares\CorsMiddleware::class);

    // Utils
    $app->route('*', '/util/{method:[\w\-]+}[/{params:.*}]', 'UtilController', 'api.util');
});
