<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 08/01/2021 Vagner Cardoso
 */

use Core\Helpers\Helper;

$startTime = microtime(true);

define('ROOT', realpath(dirname(__DIR__)));
define('PUBLIC_FOLDER', __DIR__.'/../public_html');
define('APP_FOLDER', __DIR__);
define('RESOURCE_FOLDER', __DIR__.'/resources');
define('BASE_URL', 'http://localhost');

require_once __DIR__.'/app/console.php';

$endTime = microtime(true);
$executionTime = number_format(($endTime - $startTime) / 60, 10);
$memoryUsed = Helper::convertBytesForHuman(memory_get_peak_usage(true));

exit("\nMemoryUsed: {$memoryUsed}\nExecutionTime: {$executionTime} minutes\n");
