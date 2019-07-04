<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 04/07/2019 Vagner Cardoso
 */

$app->route('get', '/', 'Web/IndexController', 'web.index');

$app->route('get,post', '/test', function ($request) use ($app) {
    return json([
        'name' => 'Vagner',
    ]);
}, 'web.test');
