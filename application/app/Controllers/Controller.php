<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author    Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @copyright 31/05/2019 Vagner Cardoso
 */

namespace App\Controller {
    use Core\App;
    use Slim\Container;
    use Slim\Http\Request;
    use Slim\Http\Response;
    use Slim\Http\StatusCode;

    /**
     * Class Controller
     *
     * @property \Slim\Collection settings
     * @property \Slim\Http\Environment environment
     * @property \Slim\Http\Request request
     * @property \Slim\Http\Response response
     * @property \Slim\Router router
     * @property \Core\View view
     * @property \Core\Session\Session session
     * @property \Core\Session\Flash flash
     * @property \Core\Mailer\Mailer mailer
     * @property \Core\Password\Password password
     * @property \Core\Encryption encryption
     * @property \Core\Jwt jwt
     * @property \Core\Logger logger
     * @property \Core\Event event
     * @property \Core\Database\Database db
     *
     * @author  Vagner Cardoso <vagnercardosoweb@gmail.com>
     */
    abstract class Controller
    {
        /**
         * @var \Slim\Http\Request
         */
        protected $request;

        /**
         * @var \Slim\Http\Response
         */
        protected $response;

        /**
         * @var \Slim\Container
         */
        protected $container;

        /**
         * @param \Slim\Http\Request  $request
         * @param \Slim\Http\Response $response
         * @param \Slim\Container     $container
         */
        public function __construct(Request $request, Response $response, Container $container)
        {
            $this->request = $request;
            $this->response = $response;
            $this->container = $container;

            $this->boot();
        }

        /**
         * @param string $name
         *
         * @return mixed
         */
        public function __get($name)
        {
            return App::getInstance()->resolve($name);
        }

        /**
         * @param string $template
         * @param array  $context
         * @param int    $status
         *
         * @return \Slim\Http\Response
         */
        public function view(string $template, ?array $context = [], ?int $status = StatusCode::HTTP_OK)
        {
            return $this->view->render($this->response, $template, $context, $status);
        }

        /**
         * @return void
         */
        protected function boot(): void
        {
        }
    }
}
