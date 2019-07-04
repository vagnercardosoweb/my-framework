<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 04/07/2019 Vagner Cardoso
 */

namespace App\Providers;

use Core\App;

/**
 * Class Provider.
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
abstract class Provider
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
     * @param string $name
     *
     * @return mixed
     */
    public function __get(string $name)
    {
        return $this->app->resolve($name);
    }

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    abstract public function register(): void;

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function boot(): void
    {
    }
}
