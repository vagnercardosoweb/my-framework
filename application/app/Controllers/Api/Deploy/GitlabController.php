<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 04/07/2019 Vagner Cardoso
 */

namespace App\Controllers\Api\Deploy;

use App\Controller\Controller;
use Slim\Http\StatusCode;

/**
 * Class GitlabController.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class GitlabController extends Controller
{
    /**
     * [POST] /api/deploy/gitlab.
     *
     * @return \Slim\Http\Response
     */
    public function post()
    {
        try {
            // Headers
            $token = $this->request->getHeaderLine('X-Gitlab-Token');
            $event = $this->request->getHeaderLine('X-Gitlab-Event');

            if (empty($token) || $token !== env('DEPLOY_KEY')) {
                throw new \Exception('Token inválid.', E_USER_ERROR);
            }

            // Body
            $body = json_decode(file_get_contents('php://input'), true);

            if (empty($body['ref'])) {
                throw new \InvalidArgumentException('Body ref empty.', E_USER_ERROR);
            }

            // Trata branch
            list($ref, $head, $branch) = explode('/', $body['ref']);

            // Muda o diretório para a raiz
            chdir(ROOT);

            // Verifica pasta .git
            if (!file_exists(ROOT.'/.git')) {
                throw new \Exception('Git not initialize.', E_USER_ERROR);
            }

            switch ($branch) {
                case 'master':
                    `git fetch origin && git reset --hard origin/master 2>&1`;
                    break;
                default:
                    throw new \Exception('Branch undefined.', E_USER_ERROR);
            }

            return json([
                'error' => false,
                'message' => 'Deploy gitlab successfully.',
            ], StatusCode::HTTP_OK);
        } catch (\Exception $e) {
            return json_error(
                $e, [], StatusCode::HTTP_BAD_REQUEST
            );
        }
    }
}
