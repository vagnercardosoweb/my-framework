<?php

/**
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author    Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @copyright 31/05/2019 Vagner Cardoso
 */

namespace App\Middlewares {

    use Slim\Http\Request;
    use Slim\Http\Response;
    use Slim\Http\StatusCode;

    /**
     * Class TokenMiddleware
     *
     * @package App\Middlewares
     * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
     */
    class TokenMiddleware extends Middleware
    {
        /**
         * @param \Slim\Http\Request $request PSR7 request
         * @param \Slim\Http\Response $response PSR7 response
         * @param callable $next Next middleware
         *
         * @return \Slim\Http\Response
         */
        public function __invoke(Request $request, Response $response, callable $next)
        {
            try {
                // Variáveis
                $payload = [];
                $type = '';
                $token = '';
                $authorization = $request->getHeaderLine('Authorization');

                // Verifica se tem o header
                // Ou CSRF
                // Ou Autenticado
                if (empty($authorization)) {
                    // Token e CSRC
                    $token = ($request->getHeaderLine('X-Csrf-Token') ?: $request->getParam('_csrfToken'));

                    if (!empty($token)) {
                        $type = 'Bearer';
                    } else {
                        // Autenticado
                        if ($this->auth) {
                            $type = 'Bearer';
                            $token = 'AUTH_SESSION_NAME';
                        } else {
                            throw new \Exception("Acesso não autorizado.", E_USER_ERROR);
                        }
                    }
                }

                // Caso tenha o header Authorization dai entra nessa condições
                // para pegar o tipo e o token separadamente
                if (preg_match('/^(Basic|Bearer)\s+(.*)/i', $authorization, $matches)) {
                    array_shift($matches);

                    if (count($matches) !== 2) {
                        throw new \Exception("Tipo de autorização e token não é válido.", E_USER_ERROR);
                    }

                    $type = trim($matches[0]);
                    $token = trim($matches[1]);
                }

                // Verifica se os tipo dos token é válido
                if (!in_array($type, ['Basic', 'Bearer'])) {
                    throw new \Exception("Tipo de autorização não é válido.", E_USER_ERROR);
                }

                // Verifica se existe o token
                if (empty($token)) {
                    throw new \Exception("Opsss! Não conseguimos identificar se sua requisição é válida. Entre em contato conosco.", E_USER_ERROR);
                }

                // Se a autorização for a básica dai entra nessa condição
                // Essa condição e espeficicamente para as apis
                if ($type === 'Basic' && $token !== env('API_KEY', env('API_KEY_BASIC', null))) {
                    throw new \Exception("Acesso negado! Esse recurso requerer autorização. Entre em contato conosco.", E_USER_ERROR);
                }

                // Verifica se o TOKEN é válido caso seja necessário a autenticação
                // e decripta o token
                if ($type === 'Bearer' && !$payload = $this->encryption->decrypt($token)) {
                    if ($token !== env('API_KEY', env('API_KEY_BASIC', null))) {
                        throw new \Exception("Opsss! Não foi possível validar sua requisição! Entre em contato conosco.", E_USER_ERROR);
                    }
                }

                // Verifica se o token tem data de expiração
                // e verifica se está expirado
                if (!empty($payload['expired']) && $payload['expired'] < time()) {
                    throw new \Exception("Sua requisição expirou! Entre em contato conosco.", E_USER_ERROR);
                }

                // Caso seja autenticado e tenha o id do usuário
                // dai e criado o serviço de autorização para usar nos controllers, models...
                // E se não existir já o serviço
                if ($type === 'Bearer' && !empty($payload['id']) && !$this->auth) {
                    unset($this->container['auth']);
                    $this->container['auth'] = function () use ($payload) {
                        //
                    };
                } else {
                    if (!$this->container->has('auth')) {
                        $this->container['auth'] = function () {
                            return false;
                        };
                    }
                }
            } catch (\Exception $e) {
                return json_error(
                    $e, [], StatusCode::HTTP_UNAUTHORIZED
                );
            }

            $response = $next($request, $response);

            return $response;
        }
    }
}
