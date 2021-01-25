<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 25/01/2021 Vagner Cardoso
 */

$documentRoot = realpath(dirname(__DIR__));

if (!isset($_SERVER['DOCUMENT_ROOT'])) {
    $documentRoot = realpath($_SERVER['DOCUMENT_ROOT']);
}

define('ROOT', str_ireplace('\\', '/', $documentRoot));
define('APP_FOLDER', sprintf('%s/application', ROOT));
define('PUBLIC_FOLDER', sprintf('%s/public_html', ROOT));
define('CONFIG_FOLDER', sprintf('%s/config', APP_FOLDER));
define('STORAGE_FOLDER', sprintf('%s/storage', APP_FOLDER));
define('RESOURCE_FOLDER', sprintf('%s/resources', APP_FOLDER));

$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
$schema = 'http';

if (
    (!empty($_SERVER['HTTPS']) && 'on' == $_SERVER['HTTPS'])
    || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && 'https' == $_SERVER['HTTP_X_FORWARDED_PROTO'])
) {
    $schema = 'https';
}

define('BASE_URL', "{$schema}://{$host}");
define('REQUEST_URI', $_SERVER['REQUEST_URI']);
define('FULL_URL', BASE_URL."{$_SERVER['REQUEST_URI']}");

$bootstrapPath = sprintf('%s/app/bootstrap.php', APP_FOLDER);
define('BOOTSTRAP_PATH', $bootstrapPath);

require_once "{$bootstrapPath}";
