<?php

/**
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author    Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @copyright 31/05/2019 Vagner Cardoso
 */

namespace App\Middlewares {

    use Core\App;
    use Slim\Http\Request;
    use Slim\Http\Response;

    /**
     * Class Middleware
     *
     * @property \Slim\Collection settings
     * @property \Slim\Http\Environment environment
     * @property \Slim\Http\Request request
     * @property \Slim\Http\Response response
     * @property \Slim\Router router
     *
     * @property \Core\View view
     * @property \Core\Session\Session session
     * @property \Core\Session\Flash flash
     * @property \Core\Mailer\Mailer mailer
     * @property \Core\Password\Password password
     * @property \Core\Encryption encryption
     * @property \Core\Jwt jwt
     * @property \Core\Logger logger
     * @property \Core\Event event
     *
     * @property \Core\Database\Database db
     *
     * @package App\Middlewares
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
         * @param \Slim\Http\Request $request PSR7 request
         * @param \Slim\Http\Response $response PSR7 response
         * @param callable $next Next middleware
         *
         * @return \Slim\Http\Response
         */
        abstract public function __invoke(Request $request, Response $response, callable $next);

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
}
