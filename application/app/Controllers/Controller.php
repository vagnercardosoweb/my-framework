<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 04/07/2019 Vagner Cardoso
 */

namespace App\Controller;

use Core\App;
use Core\Router;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\StatusCode;

/**
 * Class Controller.
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
    public function view(string $template, array $context = [], ?int $status = StatusCode::HTTP_OK)
    {
        return $this->view->render($this->response, $template, $context, $status);
    }

    /**
     * @param string $template
     * @param array  $context
     *
     * @return string
     */
    public function viewFetch(string $template, array $context = []): string
    {
        return $this->view->fetch($template, $context);
    }

    /**
     * @param mixed $data
     * @param int   $status
     * @param int   $options
     *
     * @return \Slim\Http\Response
     */
    public function json($data, int $status = StatusCode::HTTP_OK, int $options = 0)
    {
        return json($data, $status, $options);
    }

    /**
     * @param string|null $message
     * @param array       $data
     * @param int         $status
     *
     * @return \Slim\Http\Response
     */
    public function jsonSuccess(?string $message = null, array $data = [], int $status = StatusCode::HTTP_OK): Response
    {
        return json_success($message, $data, $status);
    }

    /**
     * @param \Exception|\Throwable $exception
     * @param array                 $data
     * @param int                   $status
     *
     * @return \Slim\Http\Response
     */
    public function jsonError($exception, array $data = [], $status = StatusCode::HTTP_BAD_REQUEST)
    {
        return json_error($exception, $data, $status);
    }

    /**
     * @param string      $name
     * @param array       $data
     * @param array       $queryParams
     * @param string|null $hash
     *
     * @return string
     */
    public function pathFor(string $name, array $data = [], array $queryParams = [], ?string $hash = null): string
    {
        return Router::pathFor($name, $data, $queryParams, $hash);
    }

    /**
     * @param string      $name
     * @param array       $data
     * @param array       $queryParams
     * @param string|null $hash
     *
     * @return \Slim\Http\Response
     */
    public function redirect(string $name, array $data = [], array $queryParams = [], string $hash = null): ?Response
    {
        return Router::redirect($name, $data, $queryParams, $hash);
    }

    /**
     * @param string|null $key
     * @param bool        $filtered
     *
     * @return array|mixed|object|null
     */
    public function getParsedBody(?string $key = null, bool $filtered = false)
    {
        $result = empty($key)
            ? $this->request->getParsedBody()
            : $this->request->getParsedBodyParam($key);

        if ($filtered) {
            $result = filter_values($result);
            $result = empty($key) ? $result : $result[0];
        }

        return $result;
    }

    /**
     * @param string|null $key
     *
     * @return array|mixed|object|null
     */
    public function getParsedBodyFiltered(?string $key = null)
    {
        return $this->getParsedBody($key, true);
    }

    /**
     * @param string|null $key
     * @param bool        $filtered
     *
     * @return array|mixed|object|null
     */
    public function getQueryParams(?string $key = null, bool $filtered = false)
    {
        $result = empty($key)
            ? $this->request->getQueryParams()
            : $this->request->getQueryParam($key);

        if ($filtered) {
            $result = filter_values($result);
            $result = empty($key) ? $result : $result[0];
        }

        return $result;
    }

    /**
     * @param string|null $key
     *
     * @return array|mixed|object|null
     */
    public function getQueryParamsFiltered(?string $key = null)
    {
        return $this->getQueryParams($key, true);
    }

    /**
     * @param string|null $key
     * @param bool        $filtered
     *
     * @return array|mixed|null
     */
    public function getParams(?string $key = null, bool $filtered = false)
    {
        $result = empty($key)
            ? $this->request->getParams()
            : $this->request->getParam($key);

        if ($filtered) {
            $result = filter_values($result);
            $result = empty($key) ? $result : $result[0];
        }

        return $result;
    }

    /**
     * @param string|null $key
     *
     * @return array|mixed|null
     */
    public function getParamsFiltered(?string $key = null)
    {
        return $this->getParams($key, true);
    }

    /**
     * @return void
     */
    protected function boot(): void
    {
    }
}
