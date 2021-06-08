<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 08/06/2021 Vagner Cardoso
 */

namespace App\Models;

use Core\Database\Model;

/**
 * Class Model.
 *
 * @property \Slim\Collection             $settings
 * @property \Slim\Http\Environment       $environment
 * @property \Slim\Http\Request           $request
 * @property \Slim\Http\Response          $response
 * @property \Slim\Router                 $router
 * @property \Core\View                   $view
 * @property \Core\Session\Session|object $session
 * @property \Core\Session\Flash|object   $flash
 * @property \Core\Mailer\Mailer          $mailer
 * @property \Core\Password\Password      $hash
 * @property \Core\Encryption             $encryption
 * @property \Core\Jwt                    $jwt
 * @property \Core\Logger                 $logger
 * @property \Core\Event                  $event
 * @property \Core\Database\Database      $db
 * @property \Core\Database\Database      $database
 * @property \Core\Curl\Curl              $curl
 * @property \Core\Redis                  $redis
 * @property \Core\Cache\Cache            $cache
 * @property \Core\Config                 $config
 * @property \Core\Translator             $translator
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
abstract class BaseModel extends Model
{
    /**
     * @param mixed|null $data
     *
     * @return array
     */
    public function toCamelCase($data = null): array
    {
        if (is_null($data)) {
            $data = $this->data;
        }

        if ($data instanceof Model) {
            $data = $data->data;
        }

        $newData = [];

        foreach ($data as $column => $value) {
            if (is_array($value) || is_object($value)) {
                $newData[$this->columnCamelCase($column)] = $this->toCamelCase($value);
            } else {
                $newData[$this->columnCamelCase($column)] = $value;
            }
        }

        return $newData;
    }

    /**
     * @param string $column
     *
     * @return string
     */
    protected function columnCamelCase(string $column): string
    {
        if (str_contains($column, '_')) {
            $column = ucwords(str_replace('_', ' ', strtolower($column)));
            $column = str_replace(' ', '', lcfirst($column));
        }

        return $column;
    }
}
