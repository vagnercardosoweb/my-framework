<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 04/07/2019 Vagner Cardoso
 */

use Core\App;
use Core\Loader;

// Minify html, js, css etc...
ob_start(function ($buffer) {
    if (!preg_match('/localhost|.dev|.local/', $_SERVER['HTTP_HOST'])) {
        $buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer);
        $buffer = preg_replace('/\r\n|\r|\n|\t/m', '', $buffer);
        $buffer = preg_replace('/^\s+|\s+$|\s+(?=\s)/m', '', $buffer);
    }

    return $buffer;
});

// Autoload.
$authload = APP_FOLDER.'/vendor/autoload.php';

if (!file_exists($authload)) {
    die(
        'Run command in terminal: <br>'.
        '<code style="background: #000; color: #fff;">composer install</code>'
    );
}

require_once "{$authload}";

// Loader app
$app = App::getInstance();
Loader::providers($app);
Loader::middlewares($app);
Loader::routes($app);
$app->run();

// Flush buffer
ob_end_flush();
