<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 18/06/2019 Vagner Cardoso
 */

namespace App\Middlewares;

use Core\App;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

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
     * @var \Core\App
     */
    protected $app;

    /**
     * @var \Slim\Container
     */
    protected $container;

    /**
     * @param \Core\App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->container = $app->getContainer();
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
        return $this->app->resolve($name);
    }
}
