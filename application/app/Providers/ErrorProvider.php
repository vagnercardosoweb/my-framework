<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 05/03/2020 Vagner Cardoso
 */

namespace App\Providers;

use Core\App;
use Core\Env;
use Core\Helpers\Helper;
use Core\Helpers\Path;
use Core\Router;
use Slim\Container;
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
     * @return string
     */
    public function name(): string
    {
        return 'errorHandler';
    }

    /**
     * @return \Closure
     */
    public function register(): \Closure
    {
        return function (Container $container) {
            return function (Request $request, Response $response, \Throwable $exception) use ($container) {
                $url = $request->getUri();
                $method = $request->getMethod();
                $status = StatusCode::HTTP_INTERNAL_SERVER_ERROR;

                if ($exception instanceof \Exception) {
                    $status = StatusCode::HTTP_BAD_REQUEST;
                }

                if (method_exists($exception, 'getStatusCode')) {
                    $status = $exception->getStatusCode();
                }

                $errors = [
                    'debug' => $container->get('settings')['displayErrorDetails'],
                    'error' => [
                        'code' => $exception->getCode(),
                        'type' => error_code_type($exception->getCode()),
                        'name' => get_class($exception),
                        'status' => $status,
                        'message' => $exception->getMessage(),
                        'route' => "({$method}) {$url}",
                        'file' => str_replace([
                            Path::app(),
                            Path::resource(),
                            Path::public_html(),
                        ], '', $exception->getFile()),
                        'line' => $exception->getLine(),
                        'trace' => explode("\n", $exception->getTraceAsString()),
                    ],
                ];

                // Emit error event
                if (!empty($container['event'])) {
                    $container['event']->emit('eventErrorHandler', $errors);
                }

                $errors['error']['sha1'] = sha1(json_encode($errors['error']));

                // Logger error
                if (!empty($container['logger']) && true === Env::get('APP_ERROR_LOGGER')) {
                    /** @var \Core\Logger $logger */
                    $logger = $container['logger']->filename('error');

                    if (true === Env::get('APP_ERROR_HTML', false)) {
                        $logger->setHtmlFormatter();
                    }

                    if (!empty(Helper::normalizeValueType(Env::get('LOGGER_SLACK_WEBHOOK_URL', null)))) {
                        $logger->setSlackWebHookHandler(
                            Env::get('LOGGER_SLACK_WEBHOOK_URL'),
                            Helper::normalizeValueType(Env::get('LOGGER_SLACK_CHANNEL', null)),
                            Helper::normalizeValueType(Env::get('LOGGER_SLACK_USERNAME', null))
                        );
                    }

                    $logger->error($exception->getMessage(), $errors['error']);
                }

                if ($this->isResponseJson($request) || !$container->offsetExists('view')) {
                    unset($errors['debug'], $errors['error']['trace']);

                    return $response->withJson($errors, $status);
                }

                $template = sprintf('@error.%s', $status);

                if (!$container['view']->exists($template)) {
                    $template = '@error.500';
                }

                return $container['view']->render($response, $template, $errors, $status);
            };
        };
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    protected function isResponseJson(Request $request): bool
    {
        return App::isCli() || $request->isXhr() || Router::hasCurrent('/api/') || App::onlyApi();
    }
}
