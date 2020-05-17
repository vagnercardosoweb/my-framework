<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 01/03/2020 Vagner Cardoso
 */

namespace App\Middlewares;

use Core\Env;
use Core\Exception\UnauthorizedException;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class TokenMiddleware.
 *
 * @property mixed $auth
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
     * @throws \Exception
     *
     * @return \Slim\Http\Response
     */
    public function __invoke(Request $request, Response $response, callable $next): Response
    {
        // Variáveis
        $type = 'Bearer';
        $token = '';
        $authorization = $request->getHeaderLine('Authorization');

        // Verifica se tem o header || CSRF || Autenticado
        if (empty($authorization)) {
            $token = $this->getToken($request);

            if (empty($token) && $this->auth) {
                $token = 'encrypted session';
            } else {
                throw new UnauthorizedException('Acesso não autorizado.');
            }
        }

        // Caso tenha o header Authorization dai entra nessa condições
        // para pegar o tipo e o token separadamente
        if (preg_match('/^(Basic|Bearer)\s+(.*)/i', $authorization, $matches)) {
            array_shift($matches);

            if (2 !== count($matches)) {
                throw new UnauthorizedException('Token mal formatado.');
            }

            $type = trim($matches[0]);
            $token = trim($matches[1]);
        }

        // Verifica se o type do token é válido
        if (!in_array($type, ['Basic', 'Bearer'])) {
            throw new UnauthorizedException('Acesso negado! Tipo do token não foi aceito.');
        }

        // Tenta descriptografar o token e caso contrário verifica
        // se é acesso normal e se o token é aceito
        if (!$payload = $this->encryption->decrypt($token)) {
            try {
                $payload = $this->jwt->decode($token);
            } catch (\Exception $e) {
                if ($token !== Env::get('API_KEY', null)) {
                    throw new UnauthorizedException('Acesso negado! Essa requisição precisa de autorização.');
                }
            }
        }

        // Verifica se o token está expirado
        if (!empty($payload['expired']) && $payload['expired'] < time()) {
            throw new UnauthorizedException('Acesso negado! Token expirado.');
        }

        // Remove container auth
        unset($this->container['auth']);

        // Busca o usuário caso tenha o id no payload
        if (!empty($payload['id'])) {
            $this->container['auth'] = function () {
                // get user logged
            };
        }

        return $next($request, $response);
    }

    /**
     * @param \Slim\Http\Request $request
     *
     * @return string
     */
    private function getToken(Request $request)
    {
        if ($request->getHeaderLine('X-Csrf-Token')) {
            return $request->getHeaderLine('X-Csrf-Token');
        }

        if ($request->getParam('_csrfToken')) {
            return $request->getParam('_csrfToken');
        }

        if ($request->getParam('jwtToken')) {
            return $request->getParam('jwtToken');
        }
    }
}
