<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 04/07/2019 Vagner Cardoso
 */

$app->group(['prefix' => '/api', 'namespace' => 'Api/'], function () use ($app) {
    // Deploy
    $app->group(['prefix' => '/deploy', 'namespace' => 'Deploy/'], function () use ($app) {
        $app->route('post', '/gitlab', 'GitlabController', 'api.deploy-gitlab', 'cors');
        $app->route('post', '/bitbucket', 'BitbucketController', 'api.deploy-bitbucket', 'cors');
    });

    // Utils
    $app->route('*', '/util/{method:[\w\-]+}[/{params:.*}]', 'UtilController', 'api.util');
});
