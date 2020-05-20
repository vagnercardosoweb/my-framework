<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 20/05/2020 Vagner Cardoso
 */

namespace App\Providers;

use Core\Interfaces\ServiceProvider;
use Slim\Container;

/**
 * Class Provider.
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
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
abstract class Provider implements ServiceProvider
{
    /**
     * @var \Slim\Container
     */
    protected $container;

    /**
     * @param \Slim\Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function __get(string $name)
    {
        if ($this->container->has($name)) {
            return $this->container->get($name);
        }

        return null;
    }
}
