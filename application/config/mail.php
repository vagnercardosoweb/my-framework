<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 19/07/2019 Vagner Cardoso
 */

return [
    'debug' => env('MAIL_DEBUG', 0), // 0 | 1 | 2
    'charset' => env('MAIL_CHARSET', null), // default utf-8
    'auth' => env('MAIL_AUTH', true),
    'secure' => env('MAIL_SECURE', 'tls'), // ssl | tls
    'host' => env('MAIL_HOST', null),
    'post' => env('MAIL_PORT', 587),
    'username' => env('MAIL_USER', null),
    'password' => env('MAIL_PASS', null),
    'from' => [
        'name' => env('MAIL_FROM_NAME', null),
        'mail' => env('MAIL_FROM_MAIL', null),
    ],
    'language' => [
        'code' => env('MAIL_LANGUAGE_CODE', null),
        'path' => env('MAIL_LANGUAGE_PATH', null),
    ],
];
