<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 07/06/2021 Vagner Cardoso
 */

use Core\App;

//function ob_output(string $buffer): string
//{
//    if (!preg_match('/localhost|.dev|.local/', $_SERVER['HTTP_HOST'])) {
//        $buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer);
//        $buffer = preg_replace('/\r\n|\r|\n|\t/m', '', $buffer);
//        $buffer = preg_replace('/^\s+|\s+$|\s+(?=\s)/m', '', $buffer);
//    }
//
//    ini_set('zlib.output_compression_level', 5);
//
//    return ob_gzhandler($buffer, 5);
//}

// Minify html, js, css etc...
ob_start('ob_gzhandler');

// Cli server
if (PHP_SAPI == 'cli-server') {
    $url = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__.$url['path'];

    if (is_file($file)) {
        return false;
    }
}

// Autoload.
$autoload = APP_FOLDER.'/vendor/autoload.php';

if (!file_exists($autoload)) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => true, 'message' => 'Run composer install']);
    exit;
}

require_once "{$autoload}";

// Loader app
$app = App::getInstance();
$app->registerProviders();
$app->registerMiddleware();
$app->registerRoutesFolder();
$app->registerEvents();
$app->run();

// Flush buffer
ob_end_flush();
