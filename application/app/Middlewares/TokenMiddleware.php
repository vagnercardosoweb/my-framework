<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 29/12/2019 Vagner Cardoso
 */

namespace App\Middlewares;

use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\StatusCode;

/**
 * Class TokenMiddleware.
 *
 * @property mixed auth
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class TokenMiddleware extends Middleware
{
    /**
     * @param \Slim\Http\Request  $request  PSR7 request
     * @param \Slim\Http\Response $response PSR7 response
     * @param callable            $next     Next middleware
     *
     * @return \Slim\Http\Response
     */
    public function __invoke(Request $request, Response $response, callable $next): Response
    {
        try {
            // Variáveis
            $type = 'Bearer';
            $token = '';
            $authorization = $request->getHeaderLine('Authorization');

            // Verifica se tem o header || CSRF || Autenticado
            if (empty($authorization)) {
                $token = ($request->getHeaderLine('X-Csrf-Token') ?? $request->getParam('_csrfToken') ?? $request->getParam('jwtToken'));

                if (empty($token) && $this->auth) {
                    $token = 'AUTH_SESSION_NAME';
                } else {
                    throw new \Exception('Acesso não autorizado.', E_USER_ERROR);
                }
            }

            // Caso tenha o header Authorization dai entra nessa condições
            // para pegar o tipo e o token separadamente
            if (preg_match('/^(Basic|Bearer)\s+(.*)/i', $authorization, $matches)) {
                array_shift($matches);

                if (2 !== count($matches)) {
                    throw new \Exception('Token mal formatado.', E_USER_ERROR);
                }

                $type = trim($matches[0]);
                $token = trim($matches[1]);
            }

            // Verifica se o type do token é válido
            if (!in_array($type, ['Basic', 'Bearer'])) {
                throw new \Exception('Acesso negado! Tipo do token não foi aceito.', E_USER_ERROR);
            }

            // Tenta descriptografar o token e caso contrário verifica
            // se é acesso normal e se o token é aceito
            if (!$payload = $this->encryption->decrypt($token)) {
                try {
                    $payload = $this->jwt->decode($token);
                } catch (\Exception $e) {
                    if ($token !== env('API_KEY', null)) {
                        throw new \Exception('Acesso negado! Essa requisição precisa de autorização.', E_USER_ERROR);
                    }
                }
            }

            // Verifica se o token está expirado
            if (!empty($payload['expired']) && $payload['expired'] < time()) {
                throw new \Exception('Acesso negado! Token expirado.', E_USER_ERROR);
            }

            // Remove container auth
            unset($this->container['auth']);

            // Busca o usuário caso tenha o id no payload
            if (!empty($payload['id'])) {
                $this->container['auth'] = function () use ($payload) {
                    // GET USER INFO
                };
            }
        } catch (\Exception $e) {
            return json_error($e, [], StatusCode::HTTP_UNAUTHORIZED);
        }

        return $next($request, $response);
    }
}
