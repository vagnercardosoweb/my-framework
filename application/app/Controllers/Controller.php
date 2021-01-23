<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 23/01/2021 Vagner Cardoso
 */

namespace App\Controllers;

use Core\Router;
use Slim\Container;
use Slim\Exception\NotFoundException;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\StatusCode;

/**
 * Class Controller.
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
     * Controller constructor.
     *
     * @param \Slim\Http\Request  $request
     * @param \Slim\Http\Response $response
     * @param \Slim\Container     $container
     */
    public function __construct(
        Request $request,
        Response $response,
        Container $container
    ) {
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
        if ($this->container->has($name)) {
            return $this->container->get($name);
        }

        return null;
    }

    /**
     * @param string $template
     * @param array  $context
     * @param int    $status
     *
     * @return \Slim\Http\Response
     */
    public function view(
        string $template,
        array $context = [],
        int $status = StatusCode::HTTP_OK
    ): Response {
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
     *
     * @return \Slim\Http\Response
     */
    public function json($data, int $status = StatusCode::HTTP_OK): Response
    {
        return json($data, $status);
    }

    /**
     * @param string|array|object $message
     * @param array|object        $data
     * @param int                 $status
     *
     * @return \Slim\Http\Response
     */
    public function jsonSuccess(
        $message = null,
        $data = [],
        int $status = StatusCode::HTTP_OK
    ): Response {
        return json_success($message, $data, $status);
    }

    /**
     * @param \Exception $exception
     * @param array      $data
     * @param int        $status
     *
     * @return \Slim\Http\Response
     */
    public function jsonError(
        \Exception $exception,
        array $data = [],
        int $status = StatusCode::HTTP_BAD_REQUEST
    ): Response {
        return json_error($exception, $data, $status);
    }

    /**
     * @param string $name
     * @param array  $data
     * @param array  $queryParams
     * @param string $hash
     *
     * @return string
     */
    public function pathFor(
        string $name,
        array $data = [],
        array $queryParams = [],
        string $hash = ''
    ): string {
        return Router::pathFor($name, $data, $queryParams, $hash);
    }

    /**
     * @param string $name
     * @param array  $data
     * @param array  $queryParams
     * @param int    $status
     * @param string $hash
     *
     * @return \Slim\Http\Response
     */
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
     * @param string $key
     *
     * @return mixed
     */
    public function getParsedBodyFiltered(?string $key = null)
    {
        return $this->getParsedBody($key, true);
    }

    /**
     * @param string $key
     * @param bool   $filter
     *
     * @return mixed
     */
    public function getParsedBody(?string $key = null, bool $filter = false)
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
     * @param string $key
     * @param bool   $filter
     *
     * @return mixed
     */
    public function getQueryParams(?string $key = null, bool $filter = false)
    {
        $data = empty($key)
            ? $this->request->getQueryParams()
            : $this->request->getQueryParam($key);

        return $this->filterParams($data, $key, $filter);
    }

    /**
     * @param string $key
     *
     * @return array|mixed|null
     */
    public function getParamsFiltered(?string $key = null)
    {
        return $this->getParams($key, true);
    }

    /**
     * @param string $key
     * @param bool   $filter
     *
     * @return mixed
     */
    public function getParams(?string $key = null, bool $filter = false)
    {
        $data = empty($key)
            ? $this->request->getParams()
            : $this->request->getParam($key);

        return $this->filterParams($data, $key, $filter);
    }

    /**
     * @param array $keys
     * @param bool  $forceKeysExists
     *
     * @return array
     */
    public function getOnlyParamsFiltered(array $keys, bool $forceKeysExists = true): array
    {
        return $this->getOnlyParams($keys, $forceKeysExists, true);
    }

    /**
     * @param array $keys
     * @param bool  $forceKeysExists
     * @param bool  $filter
     *
     * @return array
     */
    public function getOnlyParams(array $keys, bool $forceKeysExists = true, bool $filter = false): array
    {
        $data = $this->request->getParams($keys) ?? [];
        $diffKeys = array_diff_key(array_flip($keys), $data);

        if ($forceKeysExists && !empty($diffKeys)) {
            foreach (array_keys($diffKeys) as $diffKey) {
                $data[$diffKey] = null;
            }
        }

        return $this->filterParams($data, null, $filter);
    }

    /**
     * @throws \Slim\Exception\NotFoundException
     */
    public function notFound()
    {
        throw new NotFoundException($this->request, $this->response);
    }

    /**
     * @return void
     */
    protected function boot(): void
    {
    }

    /**
     * @param mixed  $data
     * @param string $key
     * @param bool   $filter
     *
     * @return mixed
     */
    private function filterParams($data, ?string $key = null, bool $filter = false)
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
