<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author    Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @copyright 31/05/2019 Vagner Cardoso
 */

$app->group('/api', function () use ($app) {
    /*
     * Deploy
     *
     * gitlab | bitbucket
     */

    $app->group('/deploy', function () use ($app) {
        $app->route('post', '/gitlab', 'Api/Deploy/GitlabController', 'api.deploy-gitlab', 'cors');
        $app->route('post', '/bitbucket', 'Api/Deploy/BitbucketController', 'api.deploy-bitbucket', 'cors');
    });

    /*
     * Criação de api dinâmicas
     */

    $app->route('get,post,put,delete,options', '/util/{method:[\w\-]+}[/{params:.*}]', 'Api/UtilController', 'api.util');
});
