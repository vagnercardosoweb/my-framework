<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 07/12/2019 Vagner Cardoso
 */

namespace App\Middlewares;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
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
     * @param \Psr\Http\Message\RequestInterface  $request  PSR7 request
     * @param \Psr\Http\Message\ResponseInterface $response PSR7 response
     * @param callable                            $next     Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        try {
            // Variáveis
            $type = '';
            $token = '';
            $authorization = $request->getHeaderLine('Authorization');

            // Verifica se tem o header || CSRF || Autenticado
            if (empty($authorization)) {
                $token = ($request->getHeaderLine('X-Csrf-Token') ?? $request->getParam('_csrfToken') ?? $request->getParam('jwtToken'));

                if (!empty($token)) {
                    $type = 'Bearer';
                } elseif ($this->auth) {
                    $type = 'Bearer';
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
