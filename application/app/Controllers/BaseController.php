<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 07/12/2019 Vagner Cardoso
 */

namespace App\Controller;

use Core\App;
use Core\Router;
use Monolog\Logger;
use Slim\Container;
use Slim\Exception\NotFoundException;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\StatusCode;

/**
 * Class BaseController.
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
 * @property \Core\Database\Connect       $connect
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
abstract class BaseController
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

    public function view(string $template, array $context = [], int $status = StatusCode::HTTP_OK): Response
    {
        return $this->view->render($this->response, $template, $context, $status);
    }

    public function viewFetch(string $template, array $context = []): string
    {
        return $this->view->fetch($template, $context);
    }

    /**
     * @param mixed $default
     *
     * @return mixed
     */
    public function config(string $name = '', $default = null)
    {
        return config($name, $default);
    }

    /**
     * @return Logger|bool
     */
    public function logger(string $message, array $context = [], string $file = '', string $type = 'info')
    {
        return logger($message, $context, $type, $file);
    }

    /**
     * @param mixed $data
     */
    public function json($data, int $status = StatusCode::HTTP_OK, int $options = 0): Response
    {
        return json($data, $status, $options);
    }

    /**
     * @param string|array|object $message
     * @param array|object        $data
     */
    public function jsonSuccess($message = null, $data = [], int $status = StatusCode::HTTP_OK): Response
    {
        return json_success($message, $data, $status);
    }

    public function jsonError(\Exception $exception, array $data = [], int $status = StatusCode::HTTP_BAD_REQUEST): Response
    {
        return json_error($exception, $data, $status);
    }

    public function pathFor(string $name, array $data = [], array $queryParams = [], string $hash = ''): string
    {
        return Router::pathFor($name, $data, $queryParams, $hash);
    }

    public function redirect(
        string $name,
        array $data = [],
        array $queryParams = [],
        int $status = StatusCode::HTTP_FOUND,
        string $hash = ''
    ): Response {
        return Router::redirect($name, $data, $queryParams, $status, $hash);
    }

    /**
     * @return mixed
     */
    public function getParsedBodyFiltered(string $key = '')
    {
        return $this->getParsedBody($key, true);
    }

    /**
     * @return mixed
     */
    public function getParsedBody(string $key = '', bool $filter = false)
    {
        $data = empty($key)
            ? $this->request->getParsedBody()
            : $this->request->getParsedBodyParam($key);

        return $this->filterParams($data, $key, $filter);
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function getQueryParamsFiltered(?string $key = null)
    {
        return $this->getQueryParams($key, true);
    }

    /**
     * @return mixed
     */
    public function getQueryParams(string $key = '', bool $filter = false)
    {
        $data = empty($key)
            ? $this->request->getQueryParams()
            : $this->request->getQueryParam($key);

        return $this->filterParams($data, $key, $filter);
    }

    /**
     * @return array|mixed|null
     */
    public function getParamsFiltered(string $key = '')
    {
        return $this->getParams($key, true);
    }

    /**
     * @return mixed
     */
    public function getParams(string $key = '', bool $filter = false)
    {
        $data = empty($key)
            ? $this->request->getParams()
            : $this->request->getParam($key);

        return $this->filterParams($data, $key, $filter);
    }

    /**
     * @throws \Slim\Exception\NotFoundException
     */
    public function notFound()
    {
        throw new NotFoundException($this->request, $this->response);
    }

    protected function boot(): void
    {
    }

    /**
     * @param mixed $data
     *
     * @return mixed
     */
    private function filterParams($data, string $key = '', bool $filter = false)
    {
        if ($filter && !empty($data)) {
            $dataArray = is_array($data);
            $data = filter_params($data);

            if (!empty($key) && !$dataArray) {
                return $data[0];
            }
        }

        return $data;
    }
}
