<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 07/06/2021 Vagner Cardoso
 */

use Core\Env;

$clientConfig = require __DIR__.'/client.php';

$mailFromMail = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';

if ('localhost' === $mailFromMail) {
    $mailFromMail .= '.dev';
}

return [
    'debug' => Env::get('MAIL_DEBUG', 0), // 0 | 1 | 2
    'charset' => Env::get('MAIL_CHARSET', null), // default utf-8
    'auth' => Env::get('MAIL_AUTH', true),
    'secure' => Env::get('MAIL_SECURE', 'tls'), // ssl | tls
    'host' => Env::get('MAIL_HOST', null),
    'port' => Env::get('MAIL_PORT', 587),
    'username' => Env::get('MAIL_USER', null),
    'password' => Env::get('MAIL_PASS', null),
    'from' => [
        'name' => Env::get('MAIL_FROM_NAME', $clientConfig['name']),
        'mail' => Env::get('MAIL_FROM_MAIL', "no-reply@{$mailFromMail}"),
    ],
    'language' => [
        'code' => Env::get('MAIL_LANGUAGE_CODE', null),
        'path' => Env::get('MAIL_LANGUAGE_PATH', null),
    ],
];
