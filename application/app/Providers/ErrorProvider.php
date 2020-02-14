<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 13/02/2020 Vagner Cardoso
 */

namespace App\Providers;

use Core\App;
use Core\Router;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\StatusCode;

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
        $this->container['phpErrorHandler'] = $this->container['errorHandler'] = function () {
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

                if (App::isCli() || $request->isXhr() || Router::hasCurrent('/api/') || App::onlyApi()) {
                    return $response->withJson($errors, StatusCode::HTTP_INTERNAL_SERVER_ERROR);
                }

                return $this->view->render(
                    $response,
                    '@error.500',
                    $errors,
                    StatusCode::HTTP_INTERNAL_SERVER_ERROR
                );
            };
        };

        $this->container['notFoundHandler'] = function () {
            return function (Request $request, Response $response) {
                $uri = urldecode($request->getUri());

                if (App::isCli() || $request->isXhr() || Router::hasCurrent('/api/') || App::onlyApi()) {
                    return $response->withJson([
                        'error' => [
                            'url' => $uri,
                            'message' => 'Error 404 (Not Found)',
                        ],
                    ], StatusCode::HTTP_NOT_FOUND);
                }

                return $this->view->render(
                    $response,
                    '@error.404',
                    ['url' => $uri],
                    StatusCode::HTTP_NOT_FOUND
                );
            };
        };

        $this->container['notAllowedHandler'] = function () {
            return function (Request $request, Response $response, $methods) {
                $uri = urldecode($request->getUri());
                $method = $request->getMethod();

                if (App::isCli() || $request->isXhr() || Router::hasCurrent('/api/') || App::onlyApi()) {
                    return $response->withJson([
                        'error' => [
                            'url' => $uri,
                            'method' => $method,
                            'methods' => implode(', ', $methods),
                            'message' => 'Error 405 (Method not Allowed)',
                        ],
                    ], StatusCode::HTTP_METHOD_NOT_ALLOWED);
                }

                return $this->view->render($response, '@error.405', [
                    'url' => $uri,
                    'method' => $method,
                    'methods' => implode(', ', $methods),
                ], StatusCode::HTTP_METHOD_NOT_ALLOWED);
            };
        };
    }
}
