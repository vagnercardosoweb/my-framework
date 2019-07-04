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
use Core\Helpers\Helper;
use Core\Router;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class ErrorProvider.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class ErrorProvider extends Provider
{
    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function register(): void
    {
        // @return \Closure
        $this->container['phpErrorHandler'] = $this->container['errorHandler'] = function () {
            /*
             * @param \Slim\Http\Request  $request
             * @param \Slim\Http\Response $response
             * @param \Exception          $exception
             *
             * @return \Psr\Http\Message\ResponseInterface
             */
            return function (Request $request, Response $response, $exception) {
                /** @var \Slim\Route $route */
                $route = $request->getAttribute('route');

                $errors = [
                    'debug' => $this->container->settings['displayErrorDetails'],
                    'error' => [
                        'code' => $exception->getCode(),
                        'file' => str_replace([PUBLIC_FOLDER, APP_FOLDER, RESOURCE_FOLDER], '', $exception->getFile()),
                        'line' => $exception->getLine(),
                        'message' => $exception->getMessage(),
                        'route' => (is_object($route) ? '('.implode(', ', $route->getMethods()).') ' : null).$request->getUri(),
                        'trace' => explode("\n", $exception->getTraceAsString()),
                    ],
                ];

                if (App::getInstance()->resolve('event')) {
                    $this->event->emit('eventErrorHandler', $errors);
                }

                if (Helper::isPhpCli() || ($request->isXhr() || Router::hasCurrent('/api/'))) {
                    return $response->withJson($errors, 500);
                }

                return $this->view->render(
                    $response,
                    '@error.500',
                    $errors,
                    500
                );
            };
        };

        // @return \Closure
        $this->container['notFoundHandler'] = function () {
            /*
             * @param \Slim\Http\Request  $request
             * @param \Slim\Http\Response $response
             *
             * @return \Psr\Http\Message\ResponseInterface
             */
            return function (Request $request, Response $response) {
                $uri = urldecode($request->getUri());

                if (Helper::isPhpCli() || ($request->isXhr() || Router::hasCurrent('/api/'))) {
                    return $response->withJson([
                        'error' => [
                            'url' => $uri,
                            'message' => 'Error 404 (Not Found)',
                        ],
                    ], 404);
                }

                return $this->view->render(
                    $response,
                    '@error.404',
                    ['url' => $uri],
                    404
                );
            };
        };

        // @return \Closure
        $this->container['notAllowedHandler'] = function () {
            /*
             * @param \Slim\Http\Request  $request
             * @param \Slim\Http\Response $response
             * @param string[]            $methods
             *
             * @return \Psr\Http\Message\ResponseInterface
             */
            return function (Request $request, Response $response, $methods) {
                $uri = urldecode($request->getUri());
                $method = $request->getMethod();

                if (Helper::isPhpCli() || ($request->isXhr() || Router::hasCurrent('/api/'))) {
                    return $response->withJson([
                        'error' => [
                            'url' => $uri,
                            'method' => $method,
                            'methods' => implode(', ', $methods),
                            'message' => 'Error 405 (Method not Allowed)',
                        ],
                    ], 405);
                }

                return $this->view->render($response, '@error.405', [
                    'url' => $uri,
                    'method' => $method,
                    'methods' => implode(', ', $methods),
                ], 405);
            };
        };
    }
}
