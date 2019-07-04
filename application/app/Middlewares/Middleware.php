<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 04/07/2019 Vagner Cardoso
 */

namespace App\Middlewares;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Container;

/**
 * Class Middleware.
 *
 * @property \Slim\Collection        $settings
 * @property \Slim\Http\Environment  $environment
 * @property \Slim\Http\Request      $request
 * @property \Slim\Http\Response     $response
 * @property \Slim\Router            $router
 * @property \Core\View              $view
 * @property \Core\Session\Session   $session
 * @property \Core\Session\Flash     $flash
 * @property \Core\Mailer\Mailer     $mailer
 * @property \Core\Password\Password $password
 * @property \Core\Encryption        $encryption
 * @property \Core\Jwt               $jwt
 * @property \Core\Logger            $logger
 * @property \Core\Event             $event
 * @property \Core\Database\Database $db
 * @property \Core\Database\Connect  $connect
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
abstract class Middleware
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
     * @param \Psr\Http\Message\RequestInterface  $request  PSR7 request
     * @param \Psr\Http\Message\ResponseInterface $response PSR7 response
     * @param callable                            $next     Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    abstract public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next);

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function __get(string $name)
    {
        if ($this->container->has($name)) {
            return $this->container->{$name};
        }

        return false;
    }
}
