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
 * Class BitbucketController.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class BitbucketController extends Controller
{
    /**
     * [POST] /api/deploy/bitbucket.
     *
     * @return \Slim\Http\Response
     */
    public function post()
    {
        try {
            // Token
            $token = $this->getParams('token');

            if ($token !== env('DEPLOY_KEY')) {
                throw new \Exception('Token inválid.', E_USER_ERROR);
            }

            // Body
            $body = json_decode(file_get_contents('php://input'), true);

            if (empty($body['push']['changes'])) {
                throw new \InvalidArgumentException('Body push changes empty.', E_USER_ERROR);
            }

            if (($countChange = count($body['push']['changes'])) > 0) {
                $lastChange = $body['push']['changes'][$countChange - 1]['new'];

                // Verify type
                if ('branch' !== $lastChange['type']) {
                    throw new \Exception('Type change new diff branch.', E_USER_ERROR);
                }

                // Muda o diretório para a raiz
                chdir(ROOT);

                // Verifica pasta .git
                if (!file_exists(ROOT.'/.git')) {
                    throw new \Exception('Git not initialize.', E_USER_ERROR);
                }

                switch ($lastChange['name']) {
                    case 'master':
                        `git fetch origin && git reset --hard origin/master 2>&1`;
                        break;
                    default:
                        throw new \Exception('Branch undefined.', E_USER_ERROR);
                }
            } else {
                throw new \Exception('Count change less than 0.', E_USER_ERROR);
            }

            return json([
                'error' => false,
                'message' => 'Deploy bitbucket successfully.',
            ], StatusCode::HTTP_OK);
        } catch (\Exception $e) {
            return json_error(
                $e, [], StatusCode::HTTP_BAD_REQUEST
            );
        }
    }
}
