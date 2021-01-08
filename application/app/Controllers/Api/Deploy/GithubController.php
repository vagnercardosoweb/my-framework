<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 08/01/2021 Vagner Cardoso
 */

namespace App\Controllers\Api\Deploy;

use App\Controllers\Controller;
use Core\Env;
use Core\Helpers\Path;

/**
 * Class GithubController.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class GithubController extends Controller
{
    /**
     * [POST] /api/deploy/github.
     *
     * @throws \Exception
     *
     * @return \Slim\Http\Response
     */
    public function post()
    {
        // Headers
        $signature = $this->request->getHeaderLine('X-Hub-Signature');
        $event = $this->request->getHeaderLine('X-GitHub-Event');
        $contentType = $this->request->getHeaderLine('Content-Type');
        $deployToken = Env::get('DEPLOY_KEY', Env::get('DEPLOY_TOKEN', null));

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

        if (!hash_equals(hash_hmac($algo, $rawBody, $deployToken), $hash)) {
            throw new \Exception("Invalid signature {$algo}={$hash}");
        }

        // Branch
        list(, , $branch) = explode('/', $jsonBody->ref);

        // Change root directory
        chdir(Path::root());

        // Verify initialize git
        if (!file_exists(Path::root('/.git'))) {
            throw new \Exception('Git not initialize.');
        }

        switch ($branch) {
            case 'master':
                shell_exec('git fetch origin && git reset --hard origin/master 2>&1');
                break;
            default:
                throw new \Exception('Branch undefined.');
        }

        return $this->jsonSuccess('Deploy successfully.');
    }
}
