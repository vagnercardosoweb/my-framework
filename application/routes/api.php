<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 23/01/2021 Vagner Cardoso
 */

$app->group(['path' => '/api', 'namespace' => 'Api/'], function () use ($app) {
    $app->route('get,post', '/util/zipcode/{p}', 'ZipCodeController');
    $app->route('get,post', '/util/modal-detail', 'ModalDetailController');

    $app->group(['path' => '/deploy', 'namespace' => 'Deploy/'], function () use ($app) {
        $app->route('post', '/gitlab', 'GitlabController');
        $app->route('post', '/github', 'GithubController');
        $app->route('post', '/bitbucket', 'BitbucketController');
    })->add(\App\Middlewares\CorsMiddleware::class);
});
