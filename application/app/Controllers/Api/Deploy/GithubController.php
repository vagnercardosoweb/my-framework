<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 29/12/2019 Vagner Cardoso
 */

namespace App\Controllers\Api\Deploy;

use App\Controller\BaseController;
use Slim\Http\StatusCode;

/**
 * Class GithubController.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class GithubController extends BaseController
{
    /**
     * [POST] /api/deploy/github.
     *
     * @return \Slim\Http\Response
     */
    public function post()
    {
        try {
            // Headers
            $signature = $this->request->getHeaderLine('X-Hub-Signature');
            $event = $this->request->getHeaderLine('X-GitHub-Event');
            $contentType = $this->request->getHeaderLine('Content-Type');

            if ('ping' === $event) {
                return $this->jsonSuccess('Ping successfully!');
            }

            if ('application/json' !== $contentType) {
                throw new \InvalidArgumentException("Content-Type {$contentType} invalid.");
            }

            // Body
            $rawBody = file_get_contents('php://input');
            $jsonBody = json_decode($rawBody);

            // Signature
            list($algo, $hash) = explode('=', $signature);

            if (!hash_equals(hash_hmac($algo, $rawBody, env('API_KEY', null)), $hash)) {
                throw new \Exception("Invalid signature {$algo}={$hash}");
            }

            // Branch
            list(, , $branch) = explode('/', $jsonBody->ref);

            // Change root directory
            chdir(ROOT);

            // Verify initialize git
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

            return $this->jsonSuccess([
                'error' => false,
                'message' => 'Deploy successfully.',
            ], StatusCode::HTTP_OK);
        } catch (\Exception $e) {
            return $this->jsonError(
                $e, [], StatusCode::HTTP_BAD_REQUEST
            );
        }
    }
}
