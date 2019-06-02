<?php

/**
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author    Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @copyright 31/05/2019 Vagner Cardoso
 */

/**
 * Diretório raiz
 */

define('ROOT', str_ireplace('\\', '/', realpath(dirname(__DIR__))));

/**
 * Diretório raiz da pasta publica
 */

define('PUBLIC_FOLDER', ROOT.'/public_html');

/**
 * Diretório raiz da aplicação
 *
 * OBS: Esse diretório não pode ser acesso pela URL
 */

define('APP_FOLDER', ROOT.'/application');

/**
 * Diretório que armazena os recursos dos assets e views
 */

define('RESOURCE_FOLDER', APP_FOLDER.'/resources');

/**
 *  Define a URL base da aplicação
 */

$schema = 'http';
$host = $_SERVER['HTTP_HOST'];

if (
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ||
    (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
) {
    $schema = 'https';
}

define('BASE_URL', "{$schema}://{$host}");

/**
 * Define a URL completa da aplicação
 */

define('REQUEST_URI', $_SERVER['REQUEST_URI']);
define('FULL_URL', BASE_URL."{$_SERVER['REQUEST_URI']}");

/**
 * Bootstrap
 *
 * Inicia a aplicação
 */

require_once APP_FOLDER.'/app/bootstrap.php';
